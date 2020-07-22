<?php
/**
 * 苹果支付功能模块
 */
namespace api\app\module\pay;

use think\Db;
use \think\Log;
use api\app\module\BaseModule;

class ApplepayModule extends BaseModule
{
    /**
     * 支付结果验证
     * @param $receiptData
     * @return bool
     */
	public static function verifyReceipt($receiptData)
	{
        // sandbox 开发环境
        $url = 'https://sandbox.itunes.apple.com/verifyReceipt';

        // prod 生产环境
        ##$url = 'https://buy.itunes.apple.com/verifyReceipt';

        $param = [
            'receipt-data' => $receiptData,
        ];
        $result = \dctool\Fun::curl_request($url, json_encode($param));
        Log::write(sprintf('%s，苹果支付请求验证接口，请求数据：%s，接收的数据：%s', __METHOD__,
            var_export($param, true),
            var_export($result,true)),
            'log');

        $aResult = json_decode($result, true);
        if ($aResult['status'] === 0) {
            // 验证成功
            return true;
        } else {
            // 验证失败
            Log::write(sprintf('%s，苹果支付验证失败，请求数据：%s，接收的数据：%s', __METHOD__,
                var_export($param, true),
                var_export($result,true)),
                'error');
            return false;
        }
	}

}

?>