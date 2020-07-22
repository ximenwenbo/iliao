<?php
/**
 * 腾讯云短信
 */
namespace api\app\module\sms;

use api\app\module\BaseModule;
use think\Db;
use think\Log;
use think\Exception;

class QcloudSmsModule extends BaseModule
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
        require_once EXTEND_PATH . "qcloudsms_php/src/index.php";

        # 获取配置参数
        $qcloudsmsConfig = cmf_get_option('sms_qcloud');

        $appid = $qcloudsmsConfig['appid'];
        $appkey = $qcloudsmsConfig['appkey'];
        $signName = $qcloudsmsConfig['login_signName'];
        $templateId = $qcloudsmsConfig['login_templateCode'];

        # 生成短信验证码
        $code = self::createSMSCode();

        # 保存发送的短信
        $autoID = self::addSmsRecord(0, $mobile, "发送登录验证码{$code}", 1);

        try {
            # 发送短信
            $ssender = new \Qcloud\Sms\SmsSingleSender($appid, $appkey);
            $result = $ssender->sendWithParam("86", $mobile, $templateId, ["$code"], $signName);
            $rsp = json_decode($result);

            if ($rsp->result != 0) {
                if ($rsp->result == 1025) {
                    self::exceptionError('验证码发送太频繁', -1010);
                    return false;
                }

                Log::write(sprintf('%s：验证码发送失败：%s', __METHOD__, var_export($rsp, true)),'error');
                self::exceptionError('验证码发送失败', -1011);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：验证码发送系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('验证码发送系统异常:' . $e->getMessage());
        }

        # 更新验证码
        $time = time();
        $data = [
            'mobile' => $mobile,
            'code' => $code,
            'update_time' => $time,
            'expire_time' => $time + 3600 // 有效期1个小时
        ];
        if (Db::name('sms_code')->where('mobile', $mobile)->count()) {
            // 手机号已存在 更新
            Db::name('sms_code')->where('mobile', $mobile)->update($data);
        } else {
            // 手机号不存在 新增
            Db::name('sms_code')->insert($data);
        }

        return true;
    }

    /**
     * 新增短信发送记录
     *
     * @param $userId
     * @param $mobile
     * @param $content
     * @param int $type
     * @return int|string
     */
    public static function addSmsRecord($userId, $mobile, $content, $type = 1)
    {
        $input = [
            'user_id'     => $userId,
            'mobile'      => $mobile,
            'content'     => $content,
            'type'        => $type, // 1:手机验证码登录
            'create_time' => time(),
        ];
        return Db::name("sms_history")->insertGetId($input);
    }

    /**
     * 生成短信验证码
     *
     * @param int $length 位数
     * @return int
     */
    private static function createSMSCode($length = 4)
    {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }
}