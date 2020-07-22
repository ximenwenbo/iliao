<?php
/**
 * 阿里短信接口自定义类
 * User: coase
 * Date: 2018/10/20
 * Time: 18:04
 */
namespace Aliyun\DySDKLite;

class SmsApi
{
    public $accessKeyId = null;
    public $accessKeySecret = null;

    /**
     * 构造器
     *
     * @param string $accessKeyId 必填，AccessKeyId
     * @param string $accessKeySecret 必填，AccessKeySecret
     */
    public function __construct($accessKeyId, $accessKeySecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * 发送验证码短信
     */
    public function sendSmsCode($signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null)
    {
        $params = array ();

        // *** 需用户填写部分 ***
        // 必填：是否启用https
        $security = false;

        // 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $this->accessKeyId;
        $accessKeySecret = $this->accessKeySecret;

        // 必填: 短信接收号码
        $params["PhoneNumbers"] = $phoneNumbers;

        // 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $signName;

        // 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        if ($templateParam) {
            $params['TemplateParam'] = $templateParam;
        }

        // 可选: 设置发送短信流水号
        if ($outId) {
            $params['OutId'] = $outId;
        }

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        require_once __DIR__ . "/SignatureHelper.php";
        $helper = new \Aliyun\DySDKLite\SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );

        return $content;
    }
}
