<?php
namespace dctxyun;

use think\Log;
use dctool\Fun;
use think\Exception;

require_once EXTEND_PATH . 'tencentcloud-sdk-php/TCloudAutoLoader.php';

// 导入对应产品模块的client
use TencentCloud\Live\V20180801\LiveClient;
// 导入要请求接口对应的Request类
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamStateRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;

/**
 * 腾讯云--云直播类方法
 */
class Liveapi extends Base
{
    // 协议请求接口 cgi
    public static $tlivecgi = 'http://fcgi.video.qcloud.com/common_access';

    /**
     * 开始混流
     * @param string $masterStream
     * @param array $aSlaveStream
     * @param string $mixStreamSessionId
     * @param int $slaveInputType
     * @return bool
     */
    public static function startMixStream($masterStream, $aSlaveStream, $mixStreamSessionId, $slaveInputType = 0)
    {
        $time = time();
        $trtc = cmf_get_option('trtc');
        $appid = $trtc['appid'];
        $key = $trtc['api_key'];
        $t = $time + 60;
        $sign = md5($key . $t);
        $url = self::$tlivecgi . "?appid={$appid}&interface=Mix_StreamV2&t={$t}&sign={$sign}";

        switch (count($aSlaveStream)) {
            case 1:
                $mix_stream_template_id = 20;break;
            case 2:
                $mix_stream_template_id = 310;break;
            case 3:
                $mix_stream_template_id = 410;break;
            case 4:
                $mix_stream_template_id = 510;break;
            case 5:
                $mix_stream_template_id = 610;break;
            default:
                return false;
        }

        $input_stream_list[] = [
            'input_stream_id' => $masterStream,
            'layout_params' => [
                'image_layer' => 1
            ]
        ];
        $image_layer = 2;
        foreach ($aSlaveStream as $item) {
            $input_stream_list[] = [
                'input_stream_id' => $item,
                'layout_params' => [
                    'image_layer' => $image_layer,
                    'input_type' => $slaveInputType
                ]
            ];
            $image_layer++;
        }

        $params = [
            'timestamp' => $time,
            'eventId' => $time,
            'interface' => [
                'interfaceName' => 'Mix_StreamV2',
                'para' => [
                    'app_id' => $appid,
                    'interface' => 'mix_streamv2.start_mix_stream_advanced',
                    'mix_stream_template_id' => $mix_stream_template_id,
                    'mix_stream_session_id' => $mixStreamSessionId,
                    'output_stream_id' => $masterStream,
                    'input_stream_list' => $input_stream_list
                ]
            ]
        ];

        try {
            $result = Fun::curl_request($url, json_encode($params));
            $aResult = json_decode($result, true);
            if (isset($aResult['code']) && $aResult['code'] == 0) {
                return true;
            }

            Log::write(sprintf('%s：请求云视频混流接口返回数据失败，参数：%s，返回：%s',
                __METHOD__, json_encode($params), var_export($result, true)),'error');
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：请求云视频混流API系统异常：%s', __METHOD__, $e->getMessage()),'error');
        }

        return false;
    }

    /**
     * 取消混流
     * @param $masterStream
     * @param $mixStreamSessionId
     * @return bool
     */
    public static function cancelMixStream($masterStream, $mixStreamSessionId)
    {
        $time = time();
        $trtc = cmf_get_option('trtc');
        $appid = $trtc['appid'];
        $key = $trtc['api_key'];
        $t = $time + 60;
        $sign = md5($key . $t);
        $url = self::$tlivecgi . "?appid={$appid}&interface=Mix_StreamV2&t={$t}&sign={$sign}";

        $params = [
            'timestamp' => $time,
            'eventId' => $time,
            'interface' => [
                'interfaceName' => 'Mix_StreamV2',
                'para' => [
                    'app_id' => $appid,
                    'interface' => 'mix_streamv2.cancel_mix_stream',
                    'mix_stream_session_id' => $mixStreamSessionId,
                    'output_stream_id' => $masterStream,
                ]
            ]
        ];

        try {
            $result = Fun::curl_request($url, json_encode($params));
            $aResult = json_decode($result, true);
            if (isset($aResult['code']) && $aResult['code'] == 0) {
                return true;
            }

            Log::write(sprintf('%s：请求云视频取消混流接口返回数据失败，masterStream：%s, mixStreamSessionId：%s，返回：%s, ',
                __METHOD__, $masterStream, $mixStreamSessionId, var_export($result, true)),'error');
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：请求云视频取消混流API系统异常：%s', __METHOD__, $e->getMessage()),'error');
        }

        return false;
    }

    /**
     * 开始混流--PK
     * @param string $launchStream
     * @param array $aAcceptStream
     * @param string $mixStreamSessionId
     * @return bool
     */
    public static function startMixStream4PK($launchStream, $acceptStream, $mixStreamSessionId)
    {
        $time = time();
        $trtc = cmf_get_option('trtc');
        $appid = $trtc['appid'];
        $key = $trtc['api_key'];
        $t = $time + 60;
        $sign = md5($key . $t);
        $url = self::$tlivecgi . "?appid={$appid}&interface=Mix_StreamV2&t={$t}&sign={$sign}";

        $input_stream_list[] = [
            "input_stream_id" => "canvas1",#修改为画布的id
            "layout_params" => [
                # image_layer为1的作为混流的背景
                # 若画布不指定宽高, 则使用image_layer最小的流的宽高，作为画布的宽高
                "image_layer" => 1,
                "input_type" => 3,
                "image_width" => 500,
                "image_height" => 800,
                "color" => "0x000000"
            ]
        ];
        $input_stream_list[] = [
            'input_stream_id' => $launchStream,
            'layout_params' => [
                'image_layer' => 2,
                #位置参数百分比与像素可以混用
                "image_width" => 0.5,
                "image_height" => 0.99,
                "location_x" => 0.00,
                "location_y" => 0.01
            ]
        ];
        $input_stream_list[] = [
            'input_stream_id' => $acceptStream,
            'layout_params' => [
                'image_layer' => 3,
                "image_width" =>0.5,
                "image_height" =>0.99,
                "location_x" => 0.5,
                "location_y" => 0.01
            ]
        ];

        $params = [
            'timestamp' => $time,
            'eventId' => $time,
            'interface' => [
                'interfaceName' => 'Mix_StreamV2',
                'para' => [
                    'app_id' => $appid,
                    'interface' => 'mix_streamv2.start_mix_stream_advanced',
                    'mix_stream_template_id' => 390,
                    'mix_stream_session_id' => $mixStreamSessionId,
                    'output_stream_id' => $launchStream,
                    'input_stream_list' => $input_stream_list
                ]
            ]
        ];

        try {
            $result = Fun::curl_request($url, json_encode($params));
            $aResult = json_decode($result, true);//var_dump($aResult);die;
            if (isset($aResult['code']) && $aResult['code'] == 0) {
                return true;
            }

            Log::write(sprintf('%s：请求云视频混流接口返回数据失败，参数：%s，返回：%s',
                __METHOD__, json_encode($params), var_export($result, true)),'error');
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：请求云视频混流API系统异常：%s', __METHOD__, $e->getMessage()),'error');
        }

        return false;
    }

    /**
     * 查询直播流状态
     * @param string $streamName
     * @return string|bool
     */
    public static function describeStreamState($streamName)
    {
        $trtc = cmf_get_option('trtc');

        try {
            // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
            $cred = new Credential($trtc['cosSecretId'], $trtc['cosSecretKey']);

            // # 实例化要请求产品(以cvm为例)的client对象
            $client = new LiveClient($cred, $trtc['cosArea']);

            // 实例化一个请求对象
            $req = new DescribeLiveStreamStateRequest();
            $req->AppName = 'live';
            $req->DomainName = $trtc['t_domain'];
            $req->StreamName = $streamName;
            // 通过client对象调用想要访问的接口，需要传入请求对象
            $resp = $client->DescribeLiveStreamState($req);

            if (isset($resp->StreamState)) {
                return $resp->StreamState;
            }

            Log::write(sprintf('%s：查询腾讯云直播流状态接口返回数据错误，$streamName：%s, 返回：%s, ',
                __METHOD__, $streamName, var_export($resp, true)),'error');
        }
        catch(TencentCloudSDKException $e) {
            Log::write(sprintf('%s：查询腾讯云直播流状态接口系统异常：%s', __METHOD__, $e->getMessage()),'error');
        }

        return false;
    }
}