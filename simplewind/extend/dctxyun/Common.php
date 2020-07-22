<?php
namespace dctxyun;
/**
 * 腾讯云--公共类方法
 */
class Common
{
    /**
     * 获取推流地址
     *
     * @param $streamId
     * @param array $aParam
     * @return string
     */
    public static function getTLiveUrl($streamId, $aParam = [])
    {
        $trtc = cmf_get_option('trtc');
        $domain = $trtc['t_domain']; // 域名
        $key = $trtc['tuiliu_key']; // 推流防盗链Key
        $txTime = dechex(strtotime('+ 24 hour'));
        $txSecret = md5($key . $streamId . $txTime);

        $aParam['txSecret'] = $txSecret;
        $aParam['txTime'] = $txTime;

        return "rtmp://" . $domain . "/live/" . $streamId . '?' . http_build_query($aParam);
    }

    /**
     * 获取拉流(播放)地址
     *
     * @param $streamId
     * @param array $aParam
     * @param bool $quick 是否加速
     * @return array
     */
    public static function getBLiveUrl($streamId, $aParam = [], $quick = false)
    {
        $trtc = cmf_get_option('trtc');
        $bizid = $trtc['bizid'];
        $domain = $trtc['b_domain']; // 域名
        $key = $trtc['tuiliu_key']; // 推流防盗链Key
        $txTime = dechex(strtotime('+ 24 hour'));
        $txSecret = md5($key . $streamId . $txTime);

        $aParam['txSecret'] = $txSecret;
        $aParam['txTime'] = $txTime;

        // 是否加速
        if ($quick) {
            $aParam['bizid'] = $bizid;
        }

        return [
            "rtmp" => "rtmp://" . $domain . "/live/" . $streamId . '?' . http_build_query($aParam),
            "http_flv" => "http://" . $domain . "/live/" . $streamId . ".flv" . '?' . http_build_query($aParam),
            "http_m3u8" => "http://" . $domain . "/live/" . $streamId . ".m3u8" . '?' . http_build_query($aParam)
        ];
    }
}