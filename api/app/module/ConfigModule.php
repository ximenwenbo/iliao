<?php
/**
 * 配置功能模块
 */
namespace api\app\module;

use think\Db;

class ConfigModule extends BaseModule
{
    /**
     * 获取金币转化比例 1元=?金币
     * @return int
     */
    public static function getCoinRate()
    {
        return \dctool\Cgf::getCoinRate();
    }

    /**
     * 金币换成钱
     *
     * @param int $coin
     * @return int 单位分
     */
    public static function coin2money($coin)
    {
        $rate = self::getCoinRate();

        return $coin / $rate * 100;
    }

    /**
     * 钱换成金币
     * @param int $amount 单位分
     * @return int
     */
    public static function money2coin($amount)
    {
        $rate = self::getCoinRate();

        return $amount / 100 * $rate;
    }

    /**
     * 转化本地服务器保存的图片的文件路径，为可以访问的url
     * @param $file
     * @return string
     */
    public static function getLocalImgUrl4Api($file)
    {
        if (strpos($file, "http") === 0) {
            $url = $file;
        } elseif (strpos($file, "https") === 0) {
            $url = $file;
        } elseif (strpos($file, "/") === 0) {
            $url = config('option.admin_domain') . $file;
        } else {
            $url = config('option.admin_domain') . '/' . $file;
        }

        return $url;
    }

    /**
     * 提现说明文案
     * @return string
     */
    public static function getTips4Withdraw()
    {
        $publicConfig = cmf_get_option('public_config');

        $withdrawRate = $publicConfig['public_config']['Withdraw']['rate'];
        $minQuota = $publicConfig['public_config']['Withdraw']['quota'];
        $coinNickname = \dctool\Cgf::getCoinNickname();
        $ret = "1、收益的".$coinNickname."可提现，充值获取的".$coinNickname."不可提现\\n2、满{$minQuota}元才可提现\\n3、预计1~2工作日到账，节假日顺延\\n4、请输入与实名信息一致的支付宝账号";

        return $ret;
    }

    /**
     * 提现说明文案
     * @return string
     */
    public static function getTips4Promotion()
    {
        $divideInto = cmf_get_option('divide_into');
        if (empty($divideInto['RechargeShare']['two'])) {
            $ret = "1、您邀请的用户充值，您可获得其消费的{$divideInto['RechargeShare']['one']}%。";
        } else {
            $ret = "1、您邀请的用户充值，第一级获得其消费的{$divideInto['RechargeShare']['one']}%，第二级获得其消费的{$divideInto['RechargeShare']['two']}%。";
        }

        if (empty($divideInto['AnchorSplit']['two'])) {
            $ret .= "\\n2、您邀请的用户认证为主播，您可获得其收益的{$divideInto['AnchorSplit']['one']}%";
        } else {
            $ret .= "\\n2、您邀请的用户认证为主播，第一级获得其收益的{$divideInto['AnchorSplit']['one']}%，第二级获得其收益的{$divideInto['AnchorSplit']['two']}%。";
        }

        return $ret;
    }

    /**
     * 获取对象域名
     * @return string
     */
    public static function getObjectDomain()
    {
        $txyunOption = cmf_get_option('trtc');

        return isset($txyunOption['cosCdn']) ? $txyunOption['cosCdn'] : '';
    }
}