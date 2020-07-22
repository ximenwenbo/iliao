<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 17:50
 */
namespace api\app\controller;

use api\app\module\FandefModule;
use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\UserModule;
use api\app\module\VipModule;
use api\app\module\SmsModule;
use api\app\module\LookModule;
use api\app\module\CoinModule;
use api\app\module\ConfigModule;
use api\app\module\MaterialModule;
use api\app\module\promotion\InviteModule;

/**
 * #####用户操作自己的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 用户绑定手机号
 * 2. 获取用户基本信息
 * 3. 修改用户基本信息
 * ``````````````````
 */
class UserController extends RestBaseController
{
    /**
     * 更新用户扩展信息
     */
    public function updateUserExtra()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'device_brand' => 'require', // 设备品牌，比如苹果,小米,华为 ...
                'device_model' => 'require', // 设备型号，比如苹果6，小米8 ...
                'device_os_version' => 'require', // 设备系统版本，比如10.0.1,ios12.1.0 ...
            ]);

            $validate->message([
                'device_brand.require'  => '缺少品牌',
                'device_model.require' => '缺少型号',
                'device_os_version.require' => '缺少系统版本',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            # 修改信息
            $fieldStr = 'device_brand,device_model,device_os_version';
            $uptData = [
                'device_brand' => $param['device_brand'],
                'device_model' => $param['device_model'],
                'device_os_version' => $param['device_os_version'],
            ];
            $upData = Db::name("user")->where('id', $userId)->field($fieldStr)->update($uptData);
            if ($upData !== false) {
                $this->success("OK");
            } else {
                $this->error('更新失败！');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
    /**
     * 用户绑定手机号
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post 参数
     *     @param string mobile 手机号
     *     @param string code 验证码
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':'绑定成功!',              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                      //返回code
     *     "msg":"请输入正确的手机格式！",                   //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"您已经绑定了手机！",                      //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function bindingMobile()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'mobile' => 'require|unique:user,mobile',
                'code' => 'require'
            ]);

            $validate->message([
                'mobile.require' => '请输入手机号!',
                'mobile.unique' => '该手机号已经绑定过了！',
                'code.require' => '请输入验证码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (!cmf_check_mobile($param['mobile'])) {
                $this->error("请输入正确的手机号!");
            }

            // 校验用户是否已经绑定了手机号
            $mobile = Db::name("user")->where('id', $userId)->value('mobile');
            if (! empty($mobile)) {
                $this->error("您已经绑定了手机!");
            }

            // 校验手机号是否已经被绑定过了
            $count = Db::name("user")->where('mobile', $param['mobile'])->count();
            if (!empty($count)) {
                $this->error("该手机号已经被绑定过了!");
            }

            # 验证码校验
            if (! SmsModule::verifySMSCode($param['mobile'], $param['code'])) {
                $this->error(SmsModule::$errMessage);
            }

            # 绑定
            Db::name("user")->where('id', $userId)->update(['mobile' => $param['mobile']]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 绑定QQ/WX
     */
    public function bindingQQWeixin()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'app_id' => 'require|in:weixin,qq',
                'openid' => 'require',
                'nickname' => 'require', // 昵称
                'avatar' => 'require', // 头像地址
                'sex' => 'require|in:0,1,2', // 性别
            ]);

            $validate->message([
                'nickname.require' => '请输入昵称!',
                'avatar.require'  => '请输入头像!',
                'sex.in'  => '性别错误!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }
            $appZh = $param['app_id']=='weixin' ? '微信' : 'qq';

            # 请求设备校验
            $deviceType = $this->request->header('XX-Device-Type');
            $allowedDeviceTypes = $this->allowedDeviceTypes;
            if (! in_array($deviceType, $allowedDeviceTypes)) {
                $this->error("请求错误,未知设备!");
            }

            // 检测用户是否已经绑定过该第三方账号
            $findThirdPartyCount = Db::name("third_party_user")
                ->where('user_id', $userId)
                ->where('app_id', $param['app_id'])
                ->count();
            if ($findThirdPartyCount) {
                $this->error("您已经绑定了" . $appZh);
            }

            // 检测该第三方账号是否已经被绑定过
            $findThirdPartyUser = Db::name("third_party_user")
                ->where('openid', $param['openid'])
                ->where('app_id', $param['app_id'])
                ->find();
            if ($findThirdPartyUser) {
                # 账号已经被绑定过了，判断是否是当前用户绑定的
                if ($findThirdPartyUser['user_id'] == $userId) {
                    $this->error("您已经绑定过了");
                } else {
                    $this->error("该账号已经绑定了其它用户");
                }
            }

            # 账号绑定
            $currentTime = time();
            $requestData = request()->param();
            $ip          = $this->request->ip(0, true);
            $result = Db::name("third_party_user")->insert([
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

            if ($result) {
                $this->success("OK");
            } else {
                $this->error("绑定失败,请重新绑定");
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户是否绑定手机/微信/qq
     */
    public function getBindingInfo()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            // 检测绑定微信
            if (Db::name("third_party_user")->where(['user_id'=>$userId, 'app_id'=>'weixin'])->count()) {
                $binding_weixin = 1;
            } else {
                $binding_weixin = 0;
            }

            // 检测绑定qq
            if (Db::name("third_party_user")->where(['user_id'=>$userId, 'app_id'=>'qq'])->count()) {
                $binding_qq = 1;
            } else {
                $binding_qq = 0;
            }

            // 检测绑定手机
            $userRow = Db::name("user")->field('mobile')->find($userId);

            $this->success("OK", [
                'binding_mobile' => $userRow['mobile'],
                'is_binding_weixin' => $binding_weixin,
                'is_binding_qq' => $binding_qq,
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户基本信息
     *
     * @version v1.0.0
     * @author coase
     * @request GET
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':'1',                    //返回code
     *     'msg':'获取成功!',              //返回message
     *     'data':{
     *         "token": "",               //登录token
     *         "user_id": 69,             //用户ID
     *         "mobile": "13046667683",   //手机号
     *         "user_nickname": "",       //用户昵称
     *         "sex": 0,                  //性别;0:保密,1:男,2:女
     *         "age": "",                 //年龄
     *         "avatar": "",              //用户头像
     *         "signature": "",           //个性签名
     *         ...
     *     }
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                      //返回code
     *     "msg":"用户不存在！",                            //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                                      //返回code
     *     "msg":"登录已失效！",                            //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function getUserInfo()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $userData = Db::name("user")->find($userId);
            if (empty($userData)) {
                $this->error('用户不存在！');
            }

            $userTokenRow = Db::name("user_token")->where('user_id', $userId)->find();
            $userSettingRow = Db::name("user_setting")->where('user_id', $userId)->find();

            // 获取我关注的人数
            $followNum = Db::name("user_follow")->where(['user_id' => $userId, 'status' => 1])->count();

            // 获取最近看过我的用户
            $lastLookMeRow = Db::name('user_look')->alias('l')
                ->join('user u', 'u.id=l.user_id')
                ->where('l.be_user_id', $userId)
                ->order('l.last_look_time', 'desc')
                ->field('u.id,u.avatar')
                ->find();
            if ($lastLookMeRow) {
                $lastLookMe = FandefModule::getAvatarFullUrl($lastLookMeRow['avatar'], true);
            } else {
                $lastLookMe = '';
            }

            // 获取最近关注我的用户
            $lastFollowMeRow = Db::name('user_follow')->alias('f')
                ->join('user u', 'u.id=f.user_id')
                ->where('f.be_user_id', $userId)
                ->where('f.status', 1)
                ->order('f.id', 'desc')
                ->field('u.id,u.avatar')
                ->find();
            if ($lastFollowMeRow) {
                $lastFollowMe = FandefModule::getAvatarFullUrl($lastFollowMeRow['avatar'], true);
            } else {
                $lastFollowMe = '';
            }

            // 获取最近我关注的用户
            $lastFollowRow = Db::name('user_follow')->alias('f')
                ->join('user u', 'u.id=f.be_user_id')
                ->where('f.user_id', $userId)
                ->where('f.status', 1)
                ->order('f.id', 'desc')
                ->field('u.id,u.avatar')
                ->find();
            if ($lastFollowRow) {
                $lastFollow = FandefModule::getAvatarFullUrl($lastFollowRow['avatar'], true);
            } else {
                $lastFollow = '';
            }

            # 拼装返回值
            $userRet = [
                'user_id' => $userData['id'],
                'token' => isset($userTokenRow['token']) ? $userTokenRow['token'] : '',
                'user_nickname' => $userData['user_nickname'],
                'sex' => $userData['sex'],
                'age' => $userData['age'],
                'mobile' => $userData['mobile'],
                'qq' => $userData['qq'],
                'weixin' => $userData['weixin'],
                'avatar' => \api\app\module\FandefModule::getAvatarFullUrl($userData['avatar'], true), // 头像
                'signature' => htmlspecialchars_decode($userData['signature']),
                'speech_introduction' => MaterialModule::getFullUrl($userData['speech_introduction']), // 语音介绍
                'album' => UserModule::formatAlbumFullUrl(htmlspecialchars_decode($userData['album'])),
                'video' => UserModule::formatVideoFullUrl(htmlspecialchars_decode($userData['video'])),
                'tags' => $userData['tags'],
                'is_vip' => VipModule::checkIsVip($userData['vip_expire_time']), // 是否vip (1:是 0:否)
                'vip_expire_time' => date('Y-m-d', $userData['vip_expire_time']), // Vip到期时间
                'be_look_num' => $userData['be_look_num'], // 看过我
                'be_follow_num' => $userData['be_follow_num'], // 关注我
                'follow_num' => $followNum, // 我关注的
                'province_name' => $userData['province_name'],
                'city_name' => $userData['city_name'],
                'district_name' => $userData['district_name'],
                'open_video' => isset($userSettingRow['open_video']) ? $userSettingRow['open_video'] : 0,
                'video_cost' => isset($userSettingRow['video_cost']) ? $userSettingRow['video_cost'] : 0,
                'open_speech' => isset($userSettingRow['open_speech']) ? $userSettingRow['open_speech'] : 0,
                'speech_cost' => isset($userSettingRow['speech_cost']) ? $userSettingRow['speech_cost'] : 0,
                'last_look_me' => $lastLookMe,
                'last_follow_me' => $lastFollowMe,
                'last_follow' => $lastFollow,
                'auth_status' => Db::name('user_auth')->where('user_id', $userId)->value('status') ? : 0,
                'daren_status' => $userData['daren_status'],
                'open_position' => $userData['open_position'],
            ];

            $this->success('OK', $userRet);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取某个用户的主页信息
     *   浏览用户主页展示的数据
     */
    public function getUserHomeByUid()
    {
        try {
            $currUserId = $this->userId;

            $validate = new Validate([
                'user_id' => 'require|integer'
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $param['user_id'];
            $fieldStr = 'id,user_type,virtual_pos,sex,age,coin,withdraw_coin,frozen_coin,withdraw_frozen_coin,used_coin,withdraw_used_coin,
                         create_time,user_status,user_nickname,avatar,signature,mobile,qq,weixin,province_name,city_name,district_name,
                         longitude,latitude,address,speech_introduction,tags,album,video,vip_expire_time,daren_status,be_follow_num,be_look_num,open_position';

            $userInfo = Db::name("user")->field($fieldStr)->find($userId);
            if (!$userInfo) {
                $this->error('用户不存在!');
            }

            # 是否关注
            if ($currUserId && Db::name('user_follow')->where(['user_id'=>$currUserId, 'be_user_id'=>$userId, 'status'=>1])->count()) {
                $userInfo['is_follow'] = 1; // 关注
            } else {
                $userInfo['is_follow'] = 0; // 未关注
            }

            # 获取用户的运营客服，如果不是机器人，则该值就是用户的id
            if ($userInfo['user_type'] == 3) {
                $prom_custom_uid = InviteModule::getInvitedUidByUId($currUserId);
                if (empty($prom_custom_uid)) {
                    $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $userInfo['id'])->value('custom_id');
                }
            }
            $userInfo['prom_custom_uid'] = !empty($prom_custom_uid) ? $prom_custom_uid : $userInfo['id'];

            // 如果相册为空，则取用户头像，并加模糊效果
            if (! empty($userInfo['album'])) {
                $album = UserModule::formatAlbumFullUrl(htmlspecialchars_decode($userInfo['album']));
            }
            if (empty($album)) {
                $album[] = [
                    'object' => $userInfo['avatar'],
                    'full_url' => FandefModule::getAvatarFullUrl($userInfo['avatar'], true)
                ];
            }
//            else {
//                if (strpos($userInfo['avatar'], 'http') === 0) {
//                    $fullUrl = $userInfo['avatar'];
//                } else {
//                    $fullUrl = \api\app\module\FandefModule::getAvatarFullUrl($userInfo['avatar'], true);
//                }
//                $album[] = [
//                    'object' => $userInfo['avatar'],
//                    'full_url' => $fullUrl
//                ];
//            }

            // 设置模拟位置机器人位置
//            if ($currUserId && $userInfo['user_type'] == 3 && $userInfo['virtual_pos'] == 1) {
//                // 设置位置
//                UserModule::setVirtualRobot($currUserId, $param['user_id']);
//
//                $posRow = Db::name('robot_pos')->where(['user_id'=>$currUserId, 'robot_id'=>$userInfo['id']])->find();
//                if ($posRow) {
//                    $userInfo['longitude'] = $posRow['longitude'];
//                    $userInfo['latitude'] = $posRow['latitude'];
//                }
//            }

            $userInfo['album'] = $album;
            $userInfo['signature'] = htmlspecialchars_decode($userInfo['signature']);
            $userInfo['video'] = UserModule::formatVideoFullUrl(htmlspecialchars_decode($userInfo['video']), $currUserId);
            $userInfo['avatar'] = \api\app\module\FandefModule::getAvatarFullUrl($userInfo['avatar'], true);
            $userInfo['speech_introduction'] = MaterialModule::getFullUrl($userInfo['speech_introduction']);
            # 获取设置
            $userSettingRow = Db::name("user_setting")->where('user_id', $userId)->find();
            $userInfo['open_video'] = isset($userSettingRow['open_video']) ? $userSettingRow['open_video'] : 0;
            $userInfo['video_cost'] = isset($userSettingRow['video_cost']) ? $userSettingRow['video_cost'] : 0;
            $userInfo['open_speech'] = isset($userSettingRow['open_speech']) ? $userSettingRow['open_speech'] : 0;
            $userInfo['speech_cost'] = isset($userSettingRow['speech_cost']) ? $userSettingRow['speech_cost'] : 0;
            $userInfo['user_id'] = $userInfo['id'];
            $userInfo['is_robot'] = $userInfo['user_type']==3 ? 1 : 0;
            unset($userInfo['id']);

            # 添加被用户看过记录
            LookModule::addLook($currUserId, $param['user_id']);

            $this->success("OK", [
                'user_info' => $userInfo,
                'extra' => [
                    'is_block' => Db::name('user_block_record')->where(['user_id'=>$currUserId,'be_user_id'=>$userId])->count() ? 1: 0
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取某个机器人用户的主页信息--客服端使用
     *   浏览用户主页展示的数据
     */
    public function getRobotUserHome4Custom()
    {
        try {
            $currUserId = $this->getUserId();

            $validate = new Validate([
                'user_id' => 'require|integer',
                'from_user_id' => 'require|integer', // 客服端的聊天页面中，当user_id是机器人时，该字段表示查看该机器人的用户id
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
                'from_user_id.require' => '请输入from_user_id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $param['user_id'];
            $fieldStr = 'id,user_type,virtual_pos,sex,age,coin,withdraw_coin,frozen_coin,withdraw_frozen_coin,used_coin,withdraw_used_coin,
                         create_time,user_status,user_nickname,avatar,signature,mobile,qq,weixin,province_name,city_name,district_name,
                         longitude,latitude,address,speech_introduction,tags,album,video,vip_expire_time,daren_status,be_follow_num,be_look_num,open_position';

            $userInfo = Db::name("user")->field($fieldStr)->find($userId);
            if (!$userInfo) {
                $this->error('用户不存在!');
            }

            # 是否关注
            if (Db::name('user_follow')->where(['user_id'=>$currUserId, 'be_user_id'=>$userId, 'status'=>1])->count()) {
                $userInfo['is_follow'] = 1; // 关注
            } else {
                $userInfo['is_follow'] = 0; // 未关注
            }

            # 获取用户的运营客服，如果不是机器人，则该值就是用户的id
            if ($userInfo['user_type'] == 3) {
                $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $userInfo['id'])->value('custom_id');
            }
            $userInfo['prom_custom_uid'] = isset($prom_custom_uid) ? $prom_custom_uid : $userInfo['id'];

            // 如果相册为空，则取用户头像，并加模糊效果
            if (! empty($userInfo['album'])) {
                $album = UserModule::formatAlbumFullUrl(htmlspecialchars_decode($userInfo['album']));
            } else {
                $album[] = [
                    'object' => $userInfo['avatar'],
                    'full_url' => MaterialModule::getFullUrl($userInfo['avatar'])
                ];
            }

            // 设置模拟位置机器人位置
            if ($userInfo['user_type'] == 3 && $userInfo['virtual_pos'] == 1) {
                // 设置位置
//                UserModule::setVirtualRobot($currUserId, $param['user_id']);

                $posRow = Db::name('robot_pos')->where(['user_id'=>$param['from_user_id'], 'robot_id'=>$userInfo['id']])->find();
                if ($posRow) {
                    $userInfo['longitude'] = $posRow['longitude'];
                    $userInfo['latitude'] = $posRow['latitude'];
                }
            }

            $userInfo['album'] = $album;
            $userInfo['signature'] = htmlspecialchars_decode($userInfo['signature']);
            $userInfo['video'] = UserModule::formatVideoFullUrl(htmlspecialchars_decode($userInfo['video']), $currUserId);
            $userInfo['avatar'] = MaterialModule::getFullUrl($userInfo['avatar']);
            $userInfo['speech_introduction'] = MaterialModule::getFullUrl($userInfo['speech_introduction']);
            # 获取设置
            $userSettingRow = Db::name("user_setting")->where('user_id', $userId)->find();
            $userInfo['open_video'] = isset($userSettingRow['open_video']) ? $userSettingRow['open_video'] : 0;
            $userInfo['video_cost'] = isset($userSettingRow['video_cost']) ? $userSettingRow['video_cost'] : 0;
            $userInfo['open_speech'] = isset($userSettingRow['open_speech']) ? $userSettingRow['open_speech'] : 0;
            $userInfo['speech_cost'] = isset($userSettingRow['speech_cost']) ? $userSettingRow['speech_cost'] : 0;
            $userInfo['user_id'] = $userInfo['id'];
            $userInfo['is_robot'] = $userInfo['user_type']==3 ? 1 : 0;
            unset($userInfo['id']);
            $userInfo['attribute_custom_user_id'] = 0;
            $userInfo['attribute_custom_user_nickname'] = '';
            $userInfo['attribute_custom_user_avatar'] = '';

            # 添加被用户看过记录
            LookModule::addLook($currUserId, $param['user_id']);

            $this->success("OK", ['user_info' => $userInfo]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户相册和视频
     */
    public function getUserAlbumVideo()
    {
        try {
            $userId = $this->request->param('user_id', 0, 'intval');
            if (!$userId) {
                $userId = $this->getUserId();
            }

            $fieldStr = 'album,video';
            $aRet = [];

            $userInfo = Db::name("user")->field($fieldStr)->find($userId);
            if (!$userInfo) {
                $this->error('用户不存在!');
            }

            $aRet['album'] = UserModule::formatAlbumFullUrl(htmlspecialchars_decode($userInfo['album']));
            $aRet['video'] = UserModule::formatVideoFullUrl(htmlspecialchars_decode($userInfo['video']));

            $this->success("OK", $aRet);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 修改用户基本信息
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
    public function modifyUserInfo()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'sex' => 'integer|in:1,2',
                'age' => 'integer',
            ]);

            $validate->message([
                'sex.in' => '性别错误!',
                'age.integer' => '年龄错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            # 修改信息
            $fieldStr = 'sex,age,user_nickname,avatar,signature,qq,weixin,province_name,city_name,district_name,speech_introduction,tags';
            $data = $param;

            Db::name("user")->where('id', $userId)->field($fieldStr)->update($data);

            $this->success('OK');

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 修改个人相册
     */
    public function modifyUserAlbum()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $album = $this->request->param('album', '', 'trim');

            # 修改信息
            $album = explode(',', $album);
            foreach ($album as $row) {
                $row = trim($row);
                if (!empty($row)) {
                    $aAlbum[] = trim($row);
                }
            }
            if (!empty($aAlbum)) {
                $upData = Db::name("user")->where('id', $userId)->update(['album' => htmlspecialchars(json_encode($aAlbum, JSON_UNESCAPED_SLASHES))]);
            } else {
                $upData = Db::name("user")->where('id', $userId)->update(['album' => '']);
            }

            if ($upData !== false) {
                $this->success('OK');
            } else {
                $this->error('修改失败！');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 修改个人视频
     */
    public function modifyUserVideo()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $video = $this->request->param('video', '', 'trim');

            $video = explode(',', $video);
            foreach ($video as $row) {
                $row = trim($row);
                if (!empty($row)) {
                    $aVideo[] = trim($row);
                }
            }
            if (!empty($aVideo)) {
                $upData = Db::name("user")->where('id', $userId)->update(['video' => htmlspecialchars(json_encode($aVideo, JSON_UNESCAPED_SLASHES))]);
            } else {
                $upData = Db::name("user")->where('id', $userId)->update(['video' => '']);
            }

            if ($upData !== false) {
                $this->success('OK');
            } else {
                $this->error('修改失败！');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 实名认证
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     *     @param string idcard_front 身份证正面
     *     @param string idcard_back 身份证背面
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"提交成功!",              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                             //返回code
     *     "msg":"提交失败！",                    //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                             //返回code
     *     "msg":"您已经认证过了！",               //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function verifyIdcard()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'idcard_front' => 'require', // 身份证正面
                'idcard_back' => 'require', // 身份证背面
                'idcard_no' => 'require', // 身份证号码
                'real_name' => 'require', // 真实姓名
            ]);

            $validate->message([
                'idcard_front.require' => '请输入身份证正面!',
                'idcard_back.require' => '请输入身份证背面!',
                'idcard_no.require' => '请输入身份证号码!',
                'real_name.require' => '请输入真实姓名!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $authRow = Db::name("user_auth")->where('user_id', $userId)->find();

            if (!empty($authRow)) {
                if ($authRow['status'] == 1) {
                    $this->error("审核中!");
                } elseif ($authRow['status'] == 2) {
                    $this->error("您已经认证过了!");
                }

                # 更新认证数据
                $updData = [
                    'idcard_front' => $param['idcard_front'],
                    'idcard_back' => $param['idcard_back'],
                    'idcard_no' => $param['idcard_no'],
                    'real_name' => $param['real_name'],
                    'status' => 1,
                    'update_time' => time()
                ];
                $result = Db::name("user_auth")->where('user_id', $userId)->update($updData);
            } else {
                # 新增认证数据
                $addData = [
                    'user_id' => $userId,
                    'idcard_front' => $param['idcard_front'],
                    'idcard_back' => $param['idcard_back'],
                    'idcard_no' => $param['idcard_no'],
                    'real_name' => $param['real_name'],
                    'status' => 1,
                    'create_time' => time()
                ];
                $result = Db::name("user_auth")->insert($addData);
            }

            if ($result) {
                # 新增消息
                $condition = [
                    'sender_id' => 0,
                    'receive_id' => 1,
                    'title' => '用户实名认证',
                    'content' => '用户('.$userId.')申请实名认证',
                    'type' => 2,
                    'read_flag' => 0,
                    'create_time' => time()
                ];
                $res = Db::name('user_message')->insert($condition);
                if (! $res) {
                    Log::write(sprintf('%s：系统错误：%s', __METHOD__, '实名认证,新增消息失败'),'error');
                }
                $this->success("OK");
            } else {
                $this->error("提交失败!");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取实名认证
     */
    public function getIdcard()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $authRow = Db::name("user_auth")->where('user_id', $userId)->find();

            if ($authRow) {
                $aRet = [
                    'status' => $authRow['status'],
                    'idcard_front' => MaterialModule::getFullUrl($authRow['idcard_front']),
                    'idcard_back' => MaterialModule::getFullUrl($authRow['idcard_back']),
                    'idcard_no' => $authRow['idcard_no'],
                    'real_name' => $authRow['real_name']
                ];
            } else {
                $aRet = [
                    'status' => 0,
                    'idcard_front' => '',
                    'idcard_back' => '',
                    'idcard_no' => '',
                    'real_name' => '',
                ];
            }

            $this->success('OK', $aRet);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 达人认证
     */
    public function verifyDaren()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
//                'speech_introduction' => 'require', // 语音介绍
                'life_photo' => 'require', // 日常生活照
            ]);

            $validate->message([
//                'speech_introduction.require' => '请输入语音介绍!',
                'life_photo.require' => '请输入日常生活照!',
            ]);

            $param = $this->request->param();
            $param['speech_introduction'] = '';

            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (Db::name('user_auth')->where('user_id', $userId)->value('status') !== 2) {
                $this->error("请先实名认证!");
            }

            $authRow = Db::name("user_daren_auth")->where('user_id', $userId)->find();

            if (!empty($authRow)) {
                if ($authRow['status'] == 1) {
                    $this->error("审核中!");
                } elseif ($authRow['status'] == 2) {
                    $this->error("您已经认证过了!");
                }

                Db::name("user")->where('id', $userId)->update(['daren_status' => 1]);

                # 更新认证数据
                $updData = [
                    'speech_introduction' => $param['speech_introduction'],
                    'life_photo' => $param['life_photo'],
                    'status' => 1,
                    'update_time' => time()
                ];
                $result = Db::name("user_daren_auth")->where('user_id', $userId)->update($updData);

            } else {
                Db::name("user")->where('id', $userId)->update(['daren_status' => 1]);

                # 新增认证数据
                $addData = [
                    'user_id' => $userId,
                    'speech_introduction' => $param['speech_introduction'],
                    'life_photo' => $param['life_photo'],
                    'status' => 1,
                    'create_time' => time()
                ];
                $result = Db::name("user_daren_auth")->insert($addData);
            }

            if ($result) {
                # 新增消息
                $condition = [
                    'sender_id' => 0,
                    'receive_id' => 1,
                    'title' => '用户主播认证',
                    'content' => '用户('.$userId.')申请主播认证',
                    'type' => 2,
                    'read_flag' => 0,
                    'create_time' => time()
                ];
                $res = Db::name('user_message')->insert($condition);
                if (! $res) {
                    Log::write(sprintf('%s：系统错误：%s', __METHOD__, '主播认证,新增消息失败'),'error');
                }
                $this->success("OK");
            } else {
                $this->error("提交失败!");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取达人认证
     */
    public function getDarenAuth()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $authRow = Db::name("user_daren_auth")->where('user_id', $userId)->find();

            if ($authRow) {
                $aRet = [
                    'status' => $authRow['status'],
                    'speech_introduction' => MaterialModule::getFullUrl($authRow['speech_introduction']),
                    'life_photo' => MaterialModule::getFullUrl($authRow['life_photo']),
                ];
            } else {
                $aRet = [
                    'status' => 0,
                    'speech_introduction' => '',
                    'life_photo' => ''
                ];
            }

            $this->success("OK", $aRet);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新用户在线状态
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"OK",                    //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                      //返回code
     *     "msg":"失败，请重试！",          //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function onlineNotice()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $tokenFind = Db::name("user_token")->where('user_id', $userId)->find();
            $isWurao = Db::name("user_setting")->where('user_id', $userId)->value('open_video');
            $time = time();
            $disTime = round( ($time - $tokenFind['last_online_time']) / 60 );

            if ($disTime < 1) {
                $this->error("请求间隔不足1分钟");
            }

            if ($tokenFind['online_status'] > 2) {
                // 直播或者聊天中，状态不变
                Db::name("user_token")->where('user_id', $userId)->update([
                    'last_online_time' => $time,
                    'total_online_times' => Db::raw('total_online_times+1'),
                    'last_online_times' => Db::raw('last_online_times+1'),
                ]);

                $this->success("OK");
            }

            if (! $isWurao) { // 勿扰状态不变
                Db::name("user_token")->where('user_id', $userId)->update([
                    'last_online_time' => $time,
                    'online_status' => 5, // 5：勿扰
                    'total_online_times' => Db::raw('total_online_times+1'),
                    'last_online_times' => Db::raw('last_online_times+1'),
                ]);
            } else {
                Db::name("user_token")->where('user_id', $userId)->update([
                    'last_online_time' => $time,
                    'online_status' => 1, // 1：在线
                    'total_online_times' => Db::raw('total_online_times+1'),
                    'last_online_times' => Db::raw('last_online_times+1'),
                ]);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新用户经纬度
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     *     @param string longitude 经度
     *     @param string latitude 纬度
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"OK",                    //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                      //返回code
     *     "msg":"失败，请重试！",          //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function updatePos()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'longitude' => 'require', // 经度
                'latitude' => 'require',  // 纬度
            ]);

            $validate->message([
                'longitude.require' => '请输入经度!',
                'latitude.require' => '请输入纬度!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userRow = Db::name("user")->find($userId);

            if (empty($param['longitude']) || empty($param['latitude'])) {
                $this->success("OK");
            }

            if ($userRow['longitude'] == $param['longitude'] && $userRow['latitude'] == $param['latitude']) {
                $this->success("OK");
            } else {
                // 更新用户最新位置信息
                $result = Db::name("user")->where('id', $userId)->update([
                    'longitude' => $param['longitude'],
                    'latitude' => $param['latitude'],
                ]);
                if ($result) {
                    /**
                    // 检测是否需要更新模拟机器人(判断依据：位移是否大于1km)
                    $distance = \dctool\Fun::calc_distance($userRow['longitude'], $userRow['latitude'], $param['longitude'], $param['latitude']);
                    if ($distance > 1) {
                        UserModule::initVirtualRobots($userId);
                    }
                     */

                    $this->success("OK");
                } else {
                    $this->error("失败，请重试!");
                }
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户经纬度
     */
    public function getPos()
    {
        // 用户必须登录
        $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require', // 用户uid
                'rob_uid' => 'require', // 虚拟用户uid
            ]);

            $validate->message([
                'user_id.require' => '请输入uid!',
                'rob_uid.require' => '请输入rob_uid!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            $userFind = Db::name("user")->find($param['user_id']);
            if (! $userFind) {
                $this->error("用户不存在");
            }
            if (empty($param['user_id'])) {
                // 获取普通用户位置
                $this->success("OK", [
                    'longitude' => $userFind['longitude'],
                    'latitude' => $userFind['latitude']
                ]);
            }

            $robotPosFind = Db::name('robot_pos')->where(['user_id'=>$param['user_id'], 'robot_id'=>$param['rob_uid']])->find();
            if ($robotPosFind) {
                // 获取虚拟用户位置
                $data = [
                    'longitude' => $robotPosFind['longitude'],
                    'latitude' => $robotPosFind['latitude']
                ];
            } else {
                $robuserFind = Db::name("user")->field('longitude,latitude')->find($param['rob_uid']);
                $data = [
                    'longitude' => $robuserFind['longitude'],
                    'latitude' => $robuserFind['latitude']
                ];
            }
            $this->success("OK", $data);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 设置一个模拟机器人位置
     */
    public function setVirtualRobot()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require',
                'robot_id' => 'require'
            ]);

            $validate->message([
                'user_id.require' => '请输入user_id',
                'robot_id.require' => '请输入robot_id',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $robotRow = Db::name("user")->find($param['robot_id']);
            if ($robotRow['user_type'] != 3 || $robotRow['virtual_pos'] != 1) {
                $this->success("OK");
            }

            UserModule::setVirtualRobot($param['user_id'], $param['robot_id']);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 我的钱包
     */
    public function myWallet()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $startTime = strtotime(date('Y-m'));
            $endTime = strtotime('next month', $startTime) - 1;

            $userRow = Db::name("user")->find($userId);
            if (empty($userRow)) {
                $this->error('您查询的用户不存在！');
            }

            // 本月提现总金币
            $withdrawCoin = Db::name('withdraw_order')
                ->where('user_id', $userId)
                ->where('status', 2)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->sum('coin');

            // 本月收益总金币
            $incomeCoin = Db::name('user_coin_record')
                ->where('user_id', $userId)
                ->where('class_id', 4)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->sum('change_coin');


            // 本月支出总金币
            $payoutCoin = Db::name('user_coin_record')
                ->where('user_id', $userId)
                ->where('class_id', 3)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->sum('change_coin');


            // 所有的金币（可提现+不可提现）
            $totalCoin = $userRow['coin'] + $userRow['withdraw_coin'];

            $this->success("OK", [
                'coin' => $userRow['coin'],
                'withdraw_coin' => $userRow['withdraw_coin'],
                'withdraw_money' => sprintf('%.2f', ConfigModule::coin2money($userRow['withdraw_coin']) / 100),
                'total_coin' => $totalCoin,
                'coin_note' => sprintf('注：1元=%d'.\dctool\Cgf::getCoinNickname(), ConfigModule::getCoinRate()),
                'proportion_desc' => ConfigModule::getTips4Withdraw(), // 提现说明文案
                'income_total_coin' => $incomeCoin, // 本月收益总金币
                'payout_total_coin' => $payoutCoin, // 本月支出总金币
                'withdraw_total_coin' => $withdrawCoin, // 本月提现总金币
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取充值记录
     *   按月份获取数据
     */
    public function getRechargeList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1',
            ]);

            $validate->message([
                'page.require' => '请输入页码',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;

            $result = Db::name('recharge_order')
                ->where('user_id', $userId)
                ->where('status', 2)
                ->field('*')
                ->order('id', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success('数据为空');
            }

            $aRet = [];
            foreach ($result as $row) {
                $aRet[] = [
                    'subject' => '充值支付成功',
                    'money' => sprintf('¥%.2f', $row['amount'] / 100),
                    'coin' => sprintf('+%d'.\dctool\Cgf::getCoinNickname(), $row['coin']),
                    'create_time' => date('Y-m-d H:i', $row['create_time']),
                ];
            }

            $this->success("OK", ['list' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取提现记录
     *   按月份获取数据
     */
    public function getWithdrawList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'date' => 'require', // 日期，每月的第一天 2019-01-01
                'page' => 'require|integer|min:1',
            ]);

            $validate->message([
                'date.require' => '请输入日期',
                'page.require' => '请输入页码',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;
            $startTime = strtotime($param['date']);
            $endTime = strtotime('next month', $startTime) - 1;

            $result = Db::name('withdraw_order')
                ->where('user_id', $userId)
                ->where('status', 2)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->field('*')
                ->order('id', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success('数据为空');
            }

            $aRet = [];
            foreach ($result as $row) {
                $aRet[] = [
                    'subject' => '提现',
                    'money' => sprintf('¥%.2f', $row['amount'] / 100),
                    'coin_zh' => sprintf('-%d'.\dctool\Cgf::getCoinNickname(), $row['coin']),
                    'withdraw_account' => $row['withdraw_account'],
                    'withdraw_name' => $row['withdraw_name'],
                    'status' => $row['status'],
                    'err_msg' => $row['err_msg'],
                    'create_time' => date('Y-m-d H:i', $row['create_time']),
                ];
            }

            // 计算总金额
            if ($iPage == 1) {
                $totalCoin = Db::name('withdraw_order')
                    ->where('user_id', $userId)
                    ->where('status', 2)
                    ->where("create_time>={$startTime} AND create_time<={$endTime}")
                    ->sum('coin');
            } else {
                $totalCoin = 0;
            }

            $this->success("OK", ['list' => $aRet, 'total' => $totalCoin]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取收入记录
     *   按月份获取数据
     */
    public function getIncomeList()
    {
        try {
            $validate = new Validate([
                'date' => 'require', // 日期，每月的第一天 2019-01-01
                'page' => 'require|integer|min:1',
            ]);

            $validate->message([
                'date.require' => '请输入日期',
                'page.require' => '请输入页码',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $iPage = $param['page'];
            $iPageSize = 15;
            $startTime = strtotime($param['date']);
            $endTime = strtotime('next month', $startTime) - 1;

            $result = Db::name('user_coin_record')
                ->where('user_id', $userId)
                ->where('class_id', 4)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->field('*')
                ->order('id', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success('数据为空');
            }

            $aRet = [];
            foreach ($result as $row) {
                if ($row['change_class_id'] == 41) {
                    // 音视频聊天收入
                    $orderRow = Db::name('chat_order')->alias('o')
                        ->join('user u', 'u.id = o.launch_uid')
                        ->where('o.id', $row['change_data_id'])
                        ->field('o.type,u.user_nickname')
                        ->find();
                    if ($orderRow['type'] == 2) {
                        $subject = sprintf('与%s语音聊天收入', $orderRow['user_nickname']);
                    } elseif ($orderRow['type'] == 3){
                        $subject = sprintf('与%s视频聊天收入', $orderRow['user_nickname']);
                    } else {
                        continue;
                    }
                } elseif ($row['change_class_id'] == 42) {// 接收礼物收入
                    // 接收礼物收入
                    $orderRow = Db::name('gift_given_order')->alias('o')
                        ->join('user u', 'u.id = o.send_uid')
                        ->where('o.id', $row['change_data_id'])
                        ->field('u.user_nickname')
                        ->find();
                    $subject = sprintf('收到%s送的礼物', $orderRow['user_nickname']);
                } elseif ($row['change_class_id'] == 43) {// 收费直播间收入
                    // 收费直播间收入
                    $liveUid = Db::name('live_in_order')->alias('o')
                        ->join('live_home l', 'l.id = o.live_id')
                        ->where('o.id', $row['change_data_id'])
                        ->field('u.user_nickname')
                        ->value('l.user_id');
                    $subject = sprintf('%s进入我的收费直播间', Db::name('user')->where('id', $liveUid)->value('user_nickname'));
                } else {
                    continue;
                }

                $aRet[] = [
                    'subject' => $subject,
                    'coin_zh' => sprintf('+%d'.\dctool\Cgf::getCoinNickname(), $row['change_coin']),
                    'create_time' => date('Y-m-d H:i', $row['create_time']),
                ];
            }


            // 计算总金币
            if ($iPage == 1) {
                $totalCoin = Db::name('user_coin_record')
                    ->where('user_id', $userId)
                    ->where('class_id', 4)
                    ->where("create_time>={$startTime} AND create_time<={$endTime}")
                    ->sum('change_coin');
            } else {
                $totalCoin = 0;
            }

            $this->success("OK", ['list' => $aRet, 'total' => $totalCoin]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取支出记录
     *   按月份获取数据
     */
    public function getPayoutList()
    {
        try {
            $validate = new Validate([
                'date' => 'require', // 日期，每月的第一天 2019-01-01
                'page' => 'require|integer|min:1',
            ]);

            $validate->message([
                'date.require' => '请输入日期',
                'page.require' => '请输入页码',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $iPage = $param['page'];
            $iPageSize = 15;
            $startTime = strtotime($param['date']);
            $endTime = strtotime('next month', $startTime) - 1;

            $result = Db::name('user_coin_record')
                ->where('user_id', $userId)
                ->where('class_id', 3)
                ->where("create_time>={$startTime} AND create_time<={$endTime}")
                ->field('*')
                ->order('id', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success('数据为空');
            }

            $aRet = [];
            foreach ($result as $row) {
                if ($row['change_class_id'] == 31) {
                    // 音视频聊天支付
                    $orderRow = Db::name('chat_order')->alias('o')
                        ->join('user u', 'u.id = o.accept_uid')
                        ->where('o.id', $row['change_data_id'])
                        ->field('o.type,u.user_nickname')
                        ->find();
                    if ($orderRow['type'] == 2) {
                        $subject = sprintf('与%s语音聊天支付', $orderRow['user_nickname']);
                    } elseif ($orderRow['type'] == 3){
                        $subject = sprintf('与%s视频聊天支付', $orderRow['user_nickname']);
                    } else {
                        continue;
                    }
                } elseif ($row['change_class_id'] == 32) {
                    // 送礼物支付
                    $orderRow = Db::name('gift_given_order')->alias('o')
                        ->join('user u', 'u.id = o.receive_uid')
                        ->where('o.id', $row['change_data_id'])
                        ->field('u.user_nickname')
                        ->find();
                    $subject = sprintf('给%s送礼物', $orderRow['user_nickname']);
                } elseif ($row['change_class_id'] == 33) {
                    // 进入收费直播间支付
                    $orderRow = Db::name('live_in_order')->alias('o')
                        ->join('user u', 'u.id = o.user_id')
                        ->where('o.id', $row['change_data_id'])
                        ->field('u.user_nickname')
                        ->find();
                    $subject = sprintf('进入%s的收费直播间', $orderRow['user_nickname']);
                } else {
                    continue;
                }
                $aRet[] = [
                    'subject' => $subject,
                    'coin_zh' => sprintf('-%d'.\dctool\Cgf::getCoinNickname(), $row['change_coin']),
                    'create_time' => date('Y-m-d H:i', $row['create_time']),
                ];
            }

            // 计算总金币
            if ($iPage == 1) {
                $totalCoin = Db::name('user_coin_record')
                    ->where('user_id', $userId)
                    ->where('class_id', 3)
                    ->where("create_time>={$startTime} AND create_time<={$endTime}")
                    ->sum('change_coin');
            } else {
                $totalCoin = 0;
            }

            $this->success("OK", ['list' => $aRet, 'total' => $totalCoin]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取金币流水列表
     *   按月份获取数据
     */
    public function getCoinRecordList()
    { return false;
        try {
            $validate = new Validate([
                'date' => 'require', // 日期 2019-01-01
                'class_id' => 'require|in:1,2,3,4', //流水类型 1:充值记录 2:提现记录 3:支出记录 4:收益记录
                'page' => 'require|integer|min:1',
            ]);

            $validate->message([
                'date.require' => '请输入日期',
                'class_id.require' => '请输入类型',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $iPage = $param['page'];
            $iPageSize = 15;

            $result = Db::name('user_coin_record')
                ->where('user_id', $userId)
                ->where('class_id', $param['class_id'])
                ->field('*')
                ->order('id', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success('数据为空',[]);
            }
            $aRet = [];
            foreach ($result as $row) {
                switch ($row['class_id']) {
                    case 1:
                        $subject = '充值支付成功';
                        break;
                    case 2:
                        $subject = '提现';break;
                    case 3:
                        $subject = '支付';break;
                    case 4:
                        $subject = '收入';break;
                    default:
                        $subject = '未定义';
                }
                if ($row['change_type'] == 1) {
                    $coinZh = '+' . $row['change_coin'];
                } else {
                    $coinZh = '-' . $row['change_coin'];
                }
                $aRet[] = [
                    'subject' => $subject,
                    'class_id' => $row['class_id'],
                    'money' => sprintf('¥%.2f', ConfigModule::coin2money($row['change_coin']) / 100),
                    'change_coin' => $coinZh,
                    'create_time' => date('Y-m-d H:i', $row['create_time']),
                ];
            }

            $this->success("OK", ['list' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 提现申请
     */
    public function withdraw()
    {
        try {
            $validate = new Validate([
                'coin' => 'require|integer',
                'type' => 'require|in:1,2', // 提现类型 1：可提现余额 2：邀请奖励
                'withdraw_account' => 'require'
            ]);

            $validate->message([
                'coin.require' => '请输入您的' . \dctool\Cgf::getCoinNickname() . '数!',
                'type.require' => '请输入提现类型!',
                'withdraw_account.unique'  => '请输入账户！',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            # 用户是否实名认证
            $authCount = Db::name('user_auth')->where(['user_id'=>$userId,'status'=>2])->count();
            if (! $authCount) {
                $this->error("实名认证后才能提现");
            }
            $publicConfig = cmf_get_option('public_config');
            $minQuota = $publicConfig['public_config']['Withdraw']['quota'];
            $minQuotaCoin = ConfigModule::money2coin($minQuota * 100);
            if ($param['coin'] < $minQuotaCoin) { // 单位分
                $this->error("提现".\dctool\Cgf::getCoinNickname()."价值不足{$minQuota}元");
            }

            # 申请提现
            $result = CoinModule::applyWithdraw(
                $userId,
                [
                    'coin' => $param['coin'],
                    'type' => $param['type'],
                    'withdraw_account' => $param['withdraw_account']
                ]
            );

            if ($result !== false) {
                $this->success("OK", ['order_no' => $result]);
            } else {
                $this->error(CoinModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取vip充值列表
     */
    public function getVipTypeList()
    {
        try {
            $userId = $this->getUserId();

            $userRow = Db::name('user')->find($userId);

//            if ($userRow['vip_expire_time'] == 0) {
//                $vip_expire_time = '未开通';
//            } elseif ($userRow['vip_expire_time'] < strtotime(date('Ymd', time()))) {
//                $vip_expire_time = '已过期';
//            } else {
//                $vip_expire_time = date('Y-m-d', $userRow['vip_expire_time']);
//            }

            $aTypeList = VipModule::getVipTypeList();

            $this->success("OK", [
                'user_nickname' => $userRow['user_nickname'],
                'avatar' => MaterialModule::getFullUrl($userRow['avatar']),
//                'vip_expire_time' => $vip_expire_time,
                'is_vip' => ($userRow['vip_expire_time'] < time()) ? 0 : 1,
                'list' => array_values($aTypeList)
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取充值金额列表
     */
    public function getRechargeAmountList()
    {
        try {
            $publicConfig = cmf_get_option('public_config');
            $rechargeCoin = $publicConfig['public_config']['RechargeCoin'];

            $coinList = explode(',', $rechargeCoin['poor']);
            foreach ($coinList as &$coin) {
                $coin = [
                    'coin_zh' => $coin . \dctool\Cgf::getCoinNickname(),
                    'money' => sprintf('%.2f', ConfigModule::coin2money($coin) / 100)
                ];
            }

            $this->success("OK", ['list' => $coinList]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户协议
     */
    public function getUserProtocol()
    {
        try {
            $settings = cmf_get_option('user_agreement');

            $this->success("OK", ['user_protocol' => htmlspecialchars_decode($settings['user_agreement'])]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取隐私协议
     */
    public function getPrivacyProtocol()
    {
        try {
            $settings = cmf_get_option('privacy_agreement');

            $this->success("OK", ['privacy_protocol' => htmlspecialchars_decode($settings['privacy_agreement'])]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 意见提交
     */
    public function feedbackPost()
    {
        try {
            $userId = $this->getUserId();

            $validate = new Validate([
                'content' => 'require',
            ]);

            $validate->message([
                'content.require' => '请输入内容!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $addFeedback = [
                'user_id' => $userId,
                'content' => $param['content'],
            ];
            $result = Db::name('feedback')->insert($addFeedback);
            if ($result) {
                $this->success("OK");
            } else {
                $this->error("提交失败，请重新提交!");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 举报用户
     */
    public function informUser()
    {
        try {
            $userId = $this->getUserId();

            $validate = new Validate([
                'user_id' => 'require|integer',
            ]);

            $validate->message([
                'user_id.require' => '请输入user_id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 拉黑用户
     */
    public function blockUser()
    {
        try {
            $userId = $this->getUserId();

            $validate = new Validate([
                'user_id' => 'require|integer',
            ]);

            $validate->message([
                'user_id.require' => '请输入user_id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (Db::name('user_block_record')->where(['user_id' => $userId, 'be_user_id' => $param['user_id']])->count()) {
                $this->error("该用户已经被拉黑过了");
            }

            Db::name('user_block_record')->insert([
                'user_id' => $userId,
                'be_user_id' => $param['user_id'],
                'create_time' => time()
            ]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 关注我的
     * @version v1.0.0
     * @author zjy
     * @request REQUEST
     * @throws
     */
    public function getConcernedUser()
    {
        //登录id
        $userId = $this->getUserId();
        if(empty($userId) || !is_numeric($userId) || $userId < 1)
        {
            $this->error('请先登录');
        }
        //分页参数
        $iPage = $this->request->param('page', 1, 'int');
        $iPageSize = 15;
        //验证用户
        $is_user = Db::name("user")->find($userId);

        if (empty($is_user)) {
            $this->error('用户不存在！');
        }
        //关注我的用户信息
        $userData = Db::name('user_follow')
                    ->field("u.sex, u.age, u.user_nickname, u.avatar, u.signature, u.vip_expire_time, f.create_time, f.user_id")
                    ->alias('f')
                    ->join('user u','u.id = f.user_id')
                    ->where("f.be_user_id = $userId and f.status = 1")
                    ->order('f.create_time DESC')
                    ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
                    ->toArray();

        $total = $userData['total'] ? $userData['total'] : 0;
        $result = [];
        $page = empty($userData['last_page']) ? 0 : $userData['last_page'] ;
        if(!empty($userData['data']))
        {
            foreach ($userData['data'] as $row) {
                //返回数据
                $result[] = [
                    'user_id' => $row['user_id'], //关注我的用户id
                    'age' => $row['age'],
                    'sex' => $row['sex'] == 1 ? '男' : '女',
                    'nickname' => $row['user_nickname'],
                    'avatar' => FandefModule::getAvatarFullUrl($row['avatar'], true), // 头像
                    'signature' => htmlspecialchars_decode($row['signature']),//签名
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
                    'create_time' => date("Y-m-d", $row['create_time']) //关注时间
                ];
            }
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }else{
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }

    }

    /**
     * 我关注的
     * @version v1.0.0
     * @author coase
     * @request REQUEST
     * @throws
     */
    public function getMyConcerned()
    {
        //登录id
        $userId = $this->getUserId();
        if(empty($userId) || !is_numeric($userId) || $userId < 1)
        {
            $this->error('请先登录');
        }
        //分页参数
        $iPage = $this->request->param('page', 1, 'int');
        $iPageSize = 15;
        //验证用户
        $is_user = Db::name("user")->find($userId);

        if (empty($is_user)) {
            $this->error('用户不存在！');
        }
        //关注我的用户信息
        $userData = Db::name('user_follow')
            ->field("u.sex, u.age, u.user_nickname, u.avatar, u.signature, u.vip_expire_time, f.create_time, f.be_user_id")
            ->alias('f')
            ->join('user u','u.id = f.be_user_id')
            ->where("f.user_id = $userId and f.status = 1")
            ->order('f.create_time DESC')
            ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
            ->toArray();

        $total = $userData['total'] ? $userData['total'] : 0;
        $result = [];
        $page = empty($userData['last_page']) ? 0 : $userData['last_page'] ;
        if(!empty($userData['data']))
        {
            foreach ($userData['data'] as $row) {
                //返回数据
                $result[] = [
                    'user_id' => $row['be_user_id'], //关注我的用户id
                    'age' => $row['age'],
                    'sex' => $row['sex'] == 1 ? '男' : '女',
                    'nickname' => $row['user_nickname'],
                    'avatar' => FandefModule::getAvatarFullUrl($row['avatar'], true), // 头像
                    'signature' => htmlspecialchars_decode($row['signature']),//签名
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
                    'create_time' => date("Y-m-d", $row['create_time']) //关注时间
                ];
            }
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }else{
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }

    }

    /**
     * 看过我的
     * @version v1.0.0
     * @author zjy
     * @request REQUEST
     * @throws
     */
    public function getLookMe()
    {
        //登录id
        $userId = $this->getUserId();
        if(empty($userId) || !is_numeric($userId) || $userId < 1)
        {
            $this->error('请先登录');
        }
        //分页参数
        $iPage = $this->request->param('page', 1, 'int');
        $iPageSize = 15;
        //验证用户
        $is_user = Db::name("user")->find($userId);
        if (empty($is_user)) {
            $this->error('用户不存在！');
        }
        //查看过我的用户信息
        $userData = Db::name('user_look')
            ->field("u.sex, u.age, u.user_nickname, u.avatar, u.signature, u.vip_expire_time, l.last_look_time, l.user_id")
            ->alias('l')
            ->join('user u','u.id = l.user_id')
            ->where("l.be_user_id = $userId")
            ->order('l.last_look_time DESC')
            ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
            ->toArray();

        //返回数据
        $total = $userData['total'] ? $userData['total'] : 0;
        $page = empty($userData['last_page']) ? 0 : $userData['last_page'] ;
        $result = [];
        if(!empty($userData['data'])){
            foreach ($userData['data'] as $row) {
                $result[] = [
                    'user_id' => $row['user_id'], //查看我的用户id
                    'age' => $row['age'],
                    'sex' => $row['sex'] == 1 ? '男' : '女',
                    'nickname' => $row['user_nickname'],
                    'avatar' => FandefModule::getAvatarFullUrl($row['avatar'], true), // 头像
                    'signature' => htmlspecialchars_decode($row['signature']),//签名
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
                    'create_time' => date("Y-m-d", $row['last_look_time']) //关注时间
                ];
            }
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }else{
            $this->success("OK", ['list' => $result, 'total_page' => $page, 'total' => $total]);
        }
    }

    /**
     * 获取音视频金币列表
     */
    public function getCostList()
    {
        try {
            $aList = [50,100,200,300,400,500,600,700,800,900,1000];

            $this->success("OK", ['list' => $aList]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
