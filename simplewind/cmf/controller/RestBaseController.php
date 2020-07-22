<?php
namespace cmf\controller;

use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Request;
use think\Config;
use think\Response;
use think\Loader;
use think\Db;

class RestBaseController
{
    //token
    protected $token = '';

    //设备类型
    protected $deviceType = '';

    protected $apiVersion;

    //应用市场
    protected $storeChannel = '';

    //用户 id
    protected $userId = 0;

    //用户
    protected $user;

    //用户类型
    protected $userType;

    //用户性别(0:保密,1:男,2:女)
    protected $userSex;

    //显示数据的涉黄级别(1-10，数值越大，表示涉黄越严重，上架审核过程中该级别应该调到5[<=5]，上架过后可以放开)
    protected $yLevel = 10;

    protected $allowedDeviceTypes = ['mobile', 'android', 'iphone', 'ipad', 'web', 'pc', 'mac', 'wxapp'];

    /**
     * @var \think\Request Request实例
     */
    protected $request;
    // 验证失败是否抛出异常
    protected $failException = false;
    // 是否批量验证
    protected $batchValidate = false;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * 架构函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        \think\Log::write(sprintf('%s，请求api接口，接收的数据，Header：%s，Param：%s', __METHOD__, var_export($request->header(),true), var_export($request->param(),true)),'log');

        if (is_null($request)) {
            $request = Request::instance();
        }

        Request::instance()->root(cmf_get_root() . '/');

        $this->request = $request;

        $this->apiVersion = $this->request->header('XX-Api-Version');
        $deviceType = $this->request->header('XX-Device-Type');
        $storeChannel = $this->request->header('XX-Store-Channel');

        // 根据Header中的device 和 version两个参数，判断需要连接到的[APP审核过程中连接的]数据库
        /**if ($deviceType == 'iphone' && $this->apiVersion == '1.0.0') { // todo 为ios上架版本
            Config::set('database', [
                'debug' => true,
                'resultset_type' => 'collection',
                'auto_timestamp' => false,
                'datetime_format' => false,
                'sql_explain' => false,
                'type' => 'mysql',
                'hostname' => '127.0.0.1',
                'database' => 'xchat_db_ios', // APP审核过程中连接的数据库
                'username' => 'xchat_db_ios',
                'password' => 'WiadxcmReGZYNaaF',
                'hostport' => '3306',
                'charset' => 'utf8mb4',
                'prefix' => 't_',
                'authcode' => '8YgVjUnC9ixqoUyygF',
            ]);
        }**/
        /**elseif ($deviceType == 'android' && in_array($this->apiVersion, ['1.0.23']) && in_array($storeChannel, ['ali_store'])) { // todo 为androi上架版本
            $this->yLevel = 5; // 'mi_store','huawei_store','ali_store','baidu_store','samsung_store','360_store'
        }**/
        /**if ($deviceType == 'android' && $storeChannel=='official') { // todo 为androi上架版本
            $this->yLevel = 10;
        }**/
        // 用户验证初始化
        $this->_initUser();

        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }

    // 初始化
    protected function _initialize()
    {
    }

    private function _initUser()
    {
        $token      = $this->request->header('XX-Token');
        $deviceType = $this->request->header('XX-Device-Type');

        if (empty($deviceType)) {
            return;
        }

        if (!in_array($deviceType, $this->allowedDeviceTypes)) {
            return;
        }

        $this->deviceType = $deviceType;

        if (empty($token)) {
            return;
        }

        $this->token = $token;

        $user = Db::name('user_token')
            ->alias('a')
            ->field('b.*')
            ->where(['token' => $token, 'device_type' => $deviceType])
            ->join('__USER__ b', 'a.user_id = b.id AND a.expire_time >' . time())
            ->find();

        if (!empty($user)) {
            $this->user     = $user;
            $this->userId   = $user['id'];
            $this->userType = $user['user_type'];
            $this->userSex  = $user['sex'];
        }

    }

    /**
     * 校验用户登录
     */
    public function verifyUserLogin()
    {
        if (empty($this->user)) {
            $this->error(['code' => 1001, 'msg' => '登录已失效!']);
        }
    }

    /**
     * 前置操作
     * @access protected
     * @param string $method 前置操作方法名
     * @param array $options 调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }
            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }
            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }


    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;
        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @param mixed $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = Loader::validate($validate);
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            } else {
                return $v->getError();
            }
        } else {
            return true;
        }
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $data = '', array $header = [])
    {
        $code   = 1;
        $data = ! empty($data) ? $data : new \StdClass();
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        /**
         * 返回字符串加密
         * 加密方法：先转成json字符串，在base64编码，...
         */
        if ($this->apiVersion >= '1000.0.0') {
            $strA = base64_encode(json_encode($result));
            $randNum = mt_rand(5, 15);
            $insertStr = dechex($randNum);
            $strAB = substr_replace($strA, $insertStr, $randNum, 0);
            $resultABC = substr_replace($strAB, $insertStr, -$randNum, 0);
            $result = $insertStr . $resultABC;
            $type = 'text';
        } else {
            $type = $this->getResponseType();
        }

        $header['Access-Control-Allow-Origin']  = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response                               = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $data = '', array $header = [])
    {
        $code = 0;
        $data = ! empty($data) ? $data : new \StdClass();
        if (is_array($msg)) {
            $code = $msg['code'];
            $msg  = $msg['msg'];
        }
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        /**
         * 返回字符串加密
         * 加密方法：先转成json字符串，在base64编码，...
         */
        if ($this->apiVersion >= '1000.0.0') {
            $strA = base64_encode(json_encode($result));
            $randNum = mt_rand(5, 15);
            $insertStr = dechex($randNum);
            $strAB = substr_replace($strA, $insertStr, $randNum, 0);
            $resultABC = substr_replace($strAB, $insertStr, -$randNum, 0);
            $result = $insertStr . $resultABC;
            $type = 'text';
        } else {
            $type = $this->getResponseType();
        }

        $header['Access-Control-Allow-Origin']  = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response                               = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $code code
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function respondJson($code = 1, $msg = '', $data = '', array $header = [])
    {
        $data = ! empty($data) ? $data : new \StdClass();
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        $type                                   = $this->getResponseType();
        $header['Access-Control-Allow-Origin']  = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response                               = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return 'json';
    }

    /**
     * 获取当前登录用户的id
     * @return int
     */
    public function getUserId()
    {
        if (empty($this->userId)) {
            $this->error(['code' => 1001, 'msg' => '用户未登录']);
        }
        return $this->userId;


    }


}