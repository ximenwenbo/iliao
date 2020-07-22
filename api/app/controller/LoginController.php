<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 10:02
 */
namespace api\app\controller;

use think\Db;
use think\Log;
use think\response\Json;
use think\Validate;
use think\Exception;
use cmf\controller\RestBaseController;

use api\app\module\LoginModule;
use api\app\module\SmsModule;
use api\app\module\UserSettingModule;
use api\app\module\promotion\InviteModule;

/**
 * #####用户登录模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 手机号+验证码登录
 * 2. 退出登录
 * ``````````````````
 */
class LoginController extends RestBaseController
{
    /**
     * 手机号+验证码登录
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header 参数
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post 参数
     *     @param string mobile 手机号
     *     @param string code 验证码
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':'1',                    //返回code
     *     'msg':'登录成功!',              //返回message
     *     'data':{
     *         "token": "",               //登录token
     *         "yuntongxin_user_sig": "", //云通信用户签名
     *         "user_id": 69,             //用户ID
     *         "mobile": "13046667683",   //手机号
     *         "user_nickname": "",       //用户昵称
     *         "sex": 0,                  //性别;0:保密,1:男,2:女
     *         "age": "",                 //年龄
     *         "avatar": "",              //用户头像
     *         "signature": "",           //个性签名
     *     }
     * }
     * {
     *     "code": 1,
     *     "msg": "手机号注册成功!",
     *     "data": {
     *         "is_register": 1           //说明是手机号第一次注册
     *     }
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                      //返回code
     *     "msg":"请输入正确的手机号！",                     //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"请输入正确的验证码！",                     //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"登录失败！",                             //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function mobileSmsLogin()
    {
        try {
            $validate = new Validate([
                'mobile' => 'require|number',
                'code'  => 'require|number',
            ]);

            $validate->message([
                'mobile.require' => '请输入手机号!',
                'code.require'  => '请输入验证码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            $currentTime = time();

            if (! cmf_check_mobile($param['mobile'])) {
                $this->error("请输入正确的手机号!");
            }

            # 验证码校验
            if (! SmsModule::verifySMSCode($param['mobile'], $param['code'])) {
                $this->error(SmsModule::$errMessage);
            }

            # 请求设备校验
            $deviceType = $this->request->header('XX-Device-Type');
            $allowedDeviceTypes = $this->allowedDeviceTypes;
            if (! in_array($deviceType, $allowedDeviceTypes)) {
                $this->error("请求错误,未知设备!");
            }

            # 若手机号不存在，先注册一条
            if (! Db::name("user")->where('mobile', $param['mobile'])->count()) {
                # 注册
                $userInput = [
                    'mobile'      => $param['mobile'], // 注册手机号
                    'create_time' => $currentTime, // 注册时间
                    'user_status' => 1, // 用户状态;0:禁用,1:正常,2:未验证
                    'user_type'   => 2, // 用户类型;1:admin;2:会员
                    'last_login_ip'   => $this->request->ip(0, true),
                    'last_login_time' => $currentTime,
                ];
                if (! $userId = Db::name("user")->insertGetId($userInput)) {
                    $this->error("手机号注册失败,请重试!");
                }
            } else {
                Db::name("user")->where('mobile', $param['mobile'])->update(['last_login_time' => $currentTime]);
            }

            # 登录
            $userRet = LoginModule::userLogin(['mobile' => $param['mobile']], $deviceType);
            if ($userRet == false) {
                $this->error(LoginModule::$errMessage);
            }

            $this->success("OK", ['user_info' => $userRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 初始化用户信息
     *  完善手机号首次注册的用户数据
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     *     @param string user_nickname 昵称
     *     @param string avatar 头像url
     *     @param string sex 性别
     *     @param string province_name 省份名称
     *     @param string city_name 城市名称
     *     @param string district_name 区县名称
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"修改成功!",              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                      //返回code
     *     "msg":"修改失败，提交表单为空！",                  //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"修改失败！",                             //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function initUserInfo()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'user_nickname' => 'require',
                'sex' => 'require|integer|in:1,2', // 性别（1:男,2:女）
                'age' => 'require|integer',
                'avatar' => 'require', // 头像地址
                'province_name' => 'require',
                'city_name' => 'require',
            ]);

            $validate->message([
                'sex.number'  => '性别错误!',
                'age.integer' => '年龄错误!',
                'avatar.require' => '头像不能为空!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            if (!empty($param['from_uid'])) {
                $param['from_uid'] = intval($param['from_uid']);
            }

            # 请求设备校验
            $deviceType = $this->request->header('XX-Device-Type');
            $allowedDeviceTypes = $this->allowedDeviceTypes;
            if (! in_array($deviceType, $allowedDeviceTypes)) {
                $this->error("请求错误,未知设备!");
            }

            # 初始化个人设置
            UserSettingModule::initUserSetting($userId);

            # 修改信息
            $fieldStr = 'user_nickname,avatar,sex,age,province_name,city_name,district_name,info_complete,longitude,latitude,y_level,from_uid';
            $uptData = [
                'age' => $param['age'],
                'user_nickname' => $param['user_nickname'],
                'avatar' => $param['avatar'],
                'sex' => $param['sex'],
                'info_complete' => 1, // 个人资料是否完善(0:未完善 1:已完善)
                'province_name' => $param['province_name'],
                'city_name' => $param['city_name'],
                'district_name' => isset($param['district_name']) ? $param['district_name'] : '',
                'longitude' => isset($param['longitude']) ? $param['longitude'] : 0,
                'latitude' => isset($param['latitude']) ? $param['latitude'] : 0,
                'y_level' => 8,
                'from_uid' => isset($param['from_uid']) ? $param['from_uid'] : 0,
            ];
            $upData = Db::name("user")->where('id', $userId)->field($fieldStr)->update($uptData);

            if (!empty($param['from_uid'])) {
                // 新增邀请关系
                Db::name("prom_invite_rela")->insert([
                    'user_id' => $userId,
                    'parent_uid' => $param['from_uid'],
                    'level' => InviteModule::getInvitedLevel($param['from_uid'])
                ]);

                // 推广注册奖励
                InviteModule::inviteUserBonusByNewUid($userId);
            }

            if ($upData !== false) {
                # 登录
                $userRet = LoginModule::userLogin(['id' => $userId], $deviceType);
                if (! $userRet) {
                    $this->error(LoginModule::$errMessage);
                }

                // 按照商户流量配置分配
                /**
                $merchantId = \api\app\module\promotion\FlowAllotModule::allotUser2Merchant($userId);
                if ($merchantId) {
                    $jobData = [ // 当前任务所需的业务数据,不能为 resource 类型，其他类型最终将转化为json形式的字符串(jobData 为对象时，存储其public属性的键值对)
                        'user_id' => $userId, // 用户id
                        'merchant_id' => $merchantId, // 商户id
                        'create_time' => time(),
                        'type' => 'merchant_custom', // 给特定商户的客服发送
                        'bizId' => uniqid()
                    ];
                } else {
                    $jobData = [ // 当前任务所需的业务数据,不能为 resource 类型，其他类型最终将转化为json形式的字符串(jobData 为对象时，存储其public属性的键值对)
                        'user_id' => $userId, // 用户id
                        'create_time' => time(),
                        'type' => 'all_custom', // 给所有客服发送
                        'bizId' => uniqid()
                    ];
                }
                // 新用户消息入库
                $jobHandlerClassName = 'app\admin\job\PromptMsgJob@newUserTask'; // 当前任务将由哪个类来负责处理。
                $jobQueueName = 'prompt_new_user_msg'; // 当前任务归属的队列名称,如果为新队列，会自动创建(bullet_screen_gift_msg:礼物消息弹幕)

                $isPushed = \think\Queue::push($jobHandlerClassName , $jobData , $jobQueueName); // 将该任务推送到消息队列，等待对应的消费者去执行
                if ($isPushed === false) {
                    Log::write(sprintf('%s：新用户消息插入队列错误，返回:%s', __METHOD__, var_export($isPushed, true)),'error');
                }
                 **/

                $this->success("OK", ['user_info' => $userRet]);
            } else {
                $this->error('初始化失败！');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 第三方app登录
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header 参数
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post 参数
     *     @param int app_id app应用id
     *     @param string openid
     *     @param string unionid
     *     @param string nickname
     *     @param string avatar
     *     @param string sex
     *     @param string access_token
     *     @param string expire_time
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':'1',                    //返回code
     *     'msg':'登录成功!',              //返回message
     *     'data':{
     *         "token": "",               //登录token
     *         "trtc_user_sig": "",       //腾讯音视频用户签名
     *         "user_id": 69,             //用户ID
     *         "mobile": "13046667683",   //手机号
     *         "user_nickname": "",       //用户昵称
     *         "sex": 0,                  //性别;0:保密,1:男,2:女
     *         "age": "",                 //年龄
     *         "avatar": "",              //用户头像
     *         "signature": "",           //个性签名
     *     }
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                      //返回code
     *     "msg":"请求错误,未知设备！",                     //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"登录失败！",                             //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function oauth()
    {
        try {
            $validate = new Validate([
                'app_id' => 'require|in:weixin,qq',
                'openid' => 'require',
                'nickname' => 'require', // 昵称
//                'avatar' => 'require', // 头像地址
                'sex' => 'require|in:0,1,2', // 性别
            ]);

            $validate->message([
                'nickname.require' => '请输入昵称!',
                'avatar.require'  => '请输入头像地址!',
                'sex.in'  => '性别错误!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            # 请求设备校验
            $deviceType = $this->request->header('XX-Device-Type');
            $allowedDeviceTypes = $this->allowedDeviceTypes;
            if (! in_array($deviceType, $allowedDeviceTypes)) {
                $this->error("请求错误,未知设备!");
            }

            $currentTime = time();
            $requestData = request()->param();
            $ip          = $this->request->ip(0, true);

            $thirdPartyUserRow = Db::name("third_party_user")
                ->where('openid', 'like', '%' . $param['openid'] . '%')
                ->where('app_id', $param['app_id'])
                ->order('id', 'desc')
                ->find();
            if ($thirdPartyUserRow) {
                if ($thirdPartyUserRow['status'] == 0) {
                    $this->error('该账号被禁用了');
                }

                # 数据存在，登录
                $userId = $thirdPartyUserRow['user_id'];
                $userData = [
                    'nickname'        => $param['nickname'],
                    'access_token'    => isset($param['access_token']) ? $param['access_token'] : '',
                    'expire_time'     => isset($param['expire_time']) ? $param['expire_time'] : 0,
                    'last_login_ip'   => $ip,
                    'last_login_time' => $currentTime,
                    'login_times'     => Db::raw('login_times+1'),
                    'more'            => json_encode($requestData)
                ];

                Db::name("third_party_user")
                    ->where('openid', $param['openid'])
                    ->where('app_id', $param['app_id'])
                    ->update($userData);

                Db::name('user')->where('id', $userId)->update(['last_login_time' => $currentTime]);

            } else {
                # 数据不存在，注册
                Db::startTrans(); // 启动事务
                try {
                    $userId = Db::name("user")->insertGetId([
                        'create_time'     => $currentTime,
                        'user_status'     => 1, // 用户状态;0:禁用,1:正常,2:未验证
                        'user_type'       => 2, // 用户类型;1:admin;2:会员
                        'user_nickname'   => $param['nickname'],
                        'sex'             => $param['sex'],
                        'avatar'          => $param['avatar'],
                        'last_login_ip'   => $ip,
                        'last_login_time' => $currentTime,
                    ]);

                    Db::name("third_party_user")->insert([
                        'nickname'        => $param['nickname'],
                        'access_token'    => isset($param['access_token']) ? $param['access_token'] : '',
                        'expire_time'     => isset($param['expire_time']) ? $param['expire_time'] : 0,
                        'openid'          => $param['openid'],
                        'union_id'        => isset($param['unionid']) ? $param['unionid'] : '',
                        'user_id'         => $userId,
                        'third_party'     => $deviceType,
                        'app_id'          => $param['app_id'],
                        'last_login_ip'   => $ip,
                        'last_login_time' => $currentTime,
                        'create_time'     => $currentTime,
                        'login_times'     => 1,
                        'status'          => 1,
                        'more'            => json_encode($requestData)
                    ]);

                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback(); // 回滚事务
                    throw new Exception($e->getMessage(), $e->getCode());
                }
            }

            # 登录
            $userRet = LoginModule::userLogin(['id' => $userId], $deviceType);
            if (! $userRet) {
                $this->error(LoginModule::$errMessage);
            }

            $this->success("OK", ['user_info' => $userRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 退出登录
     *
     * @version v1.0.0
     * @author coase
     * @request POST\GET
     *
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                    //返回code
     *     'msg':'退出成功!',            //返回message
     *     'data':''
     * }
     * ``````````````````
     */
    public function logout()
    {
        try {
            $userId = $this->getUserId();
            Db::name('user_token')->where([
                'token'       => $this->token,
                'user_id'     => $userId,
                'device_type' => $this->deviceType
            ])->update(['token' => '','online_status' => 0]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
