<?php
/**
 * 登录功能模块
 * Class LoginModule
 * @package api\app\module
 */
namespace api\app\module;
use think\Db;
use api\app\module\aliyun\AliyunOssModule;
use api\app\module\txyun\YuntongxinModule;

class LoginModule extends BaseModule
{
    /**
     * 用户登录
     *   检测用户是否合法，检测用户登录token，获取腾讯音视频用户签名
     *
     * @param $findUserWhere
     * @param $deviceType
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function userLogin($findUserWhere, $deviceType)
    {
        # 查询用户，校验账户合法性
        $findUser = Db::name('user')->where($findUserWhere)->find();
        if (empty($findUser)) {
            self::exceptionError('用户不存在', -1000);
            return false;
        } else {
            switch ($findUser['user_status']) {
                case 0:
                    self::exceptionError('您已被拉黑', -1001);
                    return false;
                case 2:
                    self::exceptionError('账户还没有验证成功', -1002);
                    return false;
                default:
                    break;
            }
        }

        # 生成token
        $token = cmf_generate_user_token($findUser['id'], $deviceType, true);

        # 初始化腾讯云通信用户签名
//        $yuntongxinUserSig = YuntongxinModule::initYuntongxinUser($findUser['id']);
//        if ($yuntongxinUserSig == false) {
//            self::exceptionError(YuntongxinModule::$errMessage, YuntongxinModule::$errCode);
//            return false;
//        }
        # 初始化腾讯音视频用户签名
        $trtcUserSig = YuntongxinModule::initTRTCUser($findUser['id']);
        if ($trtcUserSig == false) {
            self::exceptionError(YuntongxinModule::$errMessage, YuntongxinModule::$errCode);
            return false;
        }

        # 拼装返回值
        $userRet = [
            'token' => $token,
//            'yuntongxin_user_sig' => $yuntongxinUserSig,
            'trtc_user_sig' => $trtcUserSig,
            'user_id' => $findUser['id'],
            'user_nickname' => $findUser['user_nickname'],
            'sex' => $findUser['sex'],
            'age' => $findUser['age'],
            'mobile' => $findUser['mobile'],
            'user_email' => $findUser['user_email'],
            'avatar' => MaterialModule::getFullUrl($findUser['avatar']),
            'signature' => htmlspecialchars_decode($findUser['signature']),
            'province_id' => $findUser['province_id'],
            'province_name' => $findUser['province_name'],
            'city_id' => $findUser['city_id'],
            'city_name' => $findUser['city_name'],
            'district_id' => $findUser['district_id'],
            'district_name' => $findUser['district_name'],
            'longitude' => (string) $findUser['longitude'],
            'latitude' => (string) $findUser['latitude'],
            'is_vip' => VipModule::checkIsVip($findUser['vip_expire_time']), // 是否vip (1:是 0:否)
            'info_complete' => $findUser['info_complete'],
        ];

        return $userRet;
    }

}