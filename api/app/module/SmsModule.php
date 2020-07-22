<?php
/**
 * 短信功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;
use api\app\module\sms\AlidySmsModule;
use api\app\module\sms\QcloudSmsModule;

class SmsModule extends BaseModule
{
    /**
     * 发送短信验证码
     *
     * @param $mobile
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function sendSMSCode($mobile)
    {
        // 当前启用的短信服务商
        $smsConf = cmf_get_option('sms_conf');

        if ($smsConf['type'] == 'aliyun_dayu') {
            // 阿里大鱼发送短信验证码
            return AlidySmsModule::sendSMSCode($mobile);
        } elseif ($smsConf['type'] == 'qcloud_sms') {
            // 腾讯云发送短信验证码
            return QcloudSmsModule::sendSMSCode($mobile);
        } else {
            throw new Exception('短信服务配置参数异常');
        }
    }

    /**
     * 验证短信验证码是否有效
     *
     * @param $mobile
     * @param $code
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function verifySMSCode($mobile, $code)
    {
        // 手机号在白名单中，则不需要验证短信验证码
        if (self::mobileWhite4Login($mobile)) {
            return true;
        }

        $smscode = Db::name('sms_code');
        $codeRow = $smscode->where(['mobile'=>$mobile, 'code'=>$code])->find();
        if (! $codeRow) {
            self::exceptionError('手机号或验证码错误', -1004);
            return false;
        }

        if (time() > $codeRow['expire_time']) {
            self::exceptionError('验证码已过期', -1005);
            return false;
        }

        return true;
    }

    /**
     * 手机号白名单
     * @param $mobile
     * @return bool
     */
    public static function mobileWhite4Login($mobile)
    {
        $publicConfig = cmf_get_option('public_config');
        $list = explode(',', $publicConfig['public_config']['White']['mobile']);

        return in_array($mobile, $list);
    }
}