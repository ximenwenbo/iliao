<?php
/**
 * 支付宝功能模块
 */
namespace api\app\module\pay;

use think\Db;
use \think\Log;
use api\app\module\BaseModule;

class AlipayModule extends BaseModule
{
	//支付宝统一下单 app支付
	public static function unifiedPay($config, $options)
	{
        # 初始化支付SDK
        require_once(EXTEND_PATH.'alipaysdk/AopSdk.php');

        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['private_key']; //请填写开发者私钥去头去尾去回车，一行字符串
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $config['public_key']; //请填写支付宝公钥，一行字符串
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeWapPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"{$options['body']}\","
            . "\"subject\": \"{$options['subject']}\","
            . "\"out_trade_no\": \"{$options['out_trade_no']}\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"{$options['total_amount']}\","
            . "\"product_code\":\"QUICK_WAP_WAY\""
            . "}";
        $request->setNotifyUrl($config['notify_url']);
        $request->setReturnUrl($options['return_url']);
        $request->setBizContent($bizcontent);
//        var_dump($request);die;

        //这里和普通的接口调用不同，使用的是pageExecute
        $response = $aop->pageExecute($request);
var_dump($response);die;
        return $response;
	}

    /**
     * 支付宝回调签名验证
     * @param $config
     * @param $post
     * @return bool
     */
	public static function callbackVerify($config, $post)
	{
        # 初始化支付SDK
        require_once(EXTEND_PATH.'alipaysdk/AopSdk.php');

        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $config['public_key']; //请填写支付宝公钥，一行字符串
        $flag = $aop->rsaCheckV1($post, NULL, "RSA2");

        return $flag;
	}
}

?>