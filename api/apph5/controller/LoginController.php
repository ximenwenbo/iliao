<?php
/**
 * User: coase
 * Date: 2019-03-19
 */
namespace api\apph5\controller;

use api\app\module\SmsModule;
use cmf\controller\HomeBaseController;
use \think\Db;
use \think\Log;
use think\Validate;
use think\Exception;

/**
 * #####H5登录页面模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.手机号登录页面
 * ``````````````````
 */
class LoginController extends HomeBaseController
{
    /**
     * 登录页
     */
    public function index(){
        $domain = $this->request->domain();
        $from_uid = $this->request->param('from_uid');
        $this->assign('domain', $domain);
        $this->assign('from_uid', $from_uid);
        return $this->fetch();
    }

    /**
     * 手机号登录验证
     */
    public function mobileLogin()
    {
        try {
            $validate = new Validate([
                'mobile' => 'require|number',
                'code'  => 'require|number',
                'from_uid'  => 'integer',
            ]);

            $validate->message([
                'mobile.require' => '请输入手机号!',
                'code.require'  => '请输入验证码!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                return json_encode(['code'=>0, 'msg'=> $validate->getError()]);
            }

            if (! cmf_check_mobile($param['mobile'])) {
                return json_encode(['code'=>0, 'msg'=> '请输入正确的手机号']);
            }

            # 验证码校验
            if (! SmsModule::verifySMSCode($param['mobile'], $param['code'])) {
                return json_encode(['code'=>0, 'msg'=> SmsModule::$errMessage]);
            }

            if (empty($param['from_uid'])) {
                return json_encode(['code'=>200, 'msg'=>'加入成功']);
            }

            # 邀请者校验
            if (! Db::name('user')->where('id', $param['from_uid'])->count()) {
                return json_encode(['code'=>200, 'msg'=>'加入成功']);
            }

            return json_encode(['code'=>200, 'msg'=>'加入成功']);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            return json_encode(['code'=>0, 'msg'=>'网络异常'.$e->getMessage()]);
        }
    }

}
