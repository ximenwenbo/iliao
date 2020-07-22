<?php
/**
 * VIP功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;

class VipModule extends BaseModule
{
    public static $vipList = [
        '1' => [
            'id' => 1,
            'subject' => '月卡',
            'money' => 30, // 单位元
            'day_cost' => '1.00', //单天收费
            'recommend' => 0, // 是否推荐 0:不推荐 1:推荐
            'icon' => [
                'title' => '30',
                'color' => 'fa8349'
            ]
        ],
        '2' => [
            'id' => 2,
            'subject' => '季卡',
            'money' => 78, // 单位元
            'day_cost' => '0.86', //单天收费
            'recommend' => 0, // 是否推荐 0:不推荐 1:推荐
            'icon' => [
                'title' => '90',
                'color' => '359df4'
            ]
        ],
        '3' => [
            'id' => 3,
            'subject' => '年卡',
            'money' => 128, // 单位元
            'day_cost' => '0.35', //单天收费
            'recommend' => 1, // 是否推荐 0:不推荐 1:推荐
            'icon' => [
                'title' => '年',
                'color' => '7e93d2'
            ]
        ]
    ];

    /**
     * 获取vip收费列表
     * @return array
     */
    public static function getVipTypeList()
    {
        $publicConfig = cmf_get_option('public_config');
        if (empty($publicConfig['public_config']['RechargeVip']['poor'])) {
            return self::$vipList;
        }

        $aList = self::$vipList;

        try {
            $aVip = explode(',', $publicConfig['public_config']['RechargeVip']['poor']);

            $aList[1]['money'] = $aVip[0];
            $aList[1]['day_cost'] = sprintf('%.2f', $aVip[0] / 30);
            $aList[2]['money'] = $aVip[1];
            $aList[2]['day_cost'] = sprintf('%.2f',$aVip[1] / 90);
            $aList[3]['money'] = $aVip[2];
            $aList[3]['day_cost'] = sprintf('%.2f',$aVip[2] / 360);
        } catch (Exception $e) {
            Log::write(sprintf('%s：获取充值vip配置错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            return self::$vipList;
        }

        return $aList;
    }

    /**
     * 获取最新的到期时间
     * @param int $currExpireTime 当前到期时间戳
     * @param int $vipType VIP类型
     * @return false|int
     */
    public static function getNewExpireTime($currExpireTime, $vipType)
    {
        if ($currExpireTime > 0) {
            $baseTime = $currExpireTime;
        } else {
            $baseTime = time();
        }
        switch ($vipType) {
            case 1:
                $newExpireTime = strtotime('+1 month', $baseTime);
                break;
            case 2:
                $newExpireTime = strtotime('+3 month', $baseTime);
                break;
            case 3:
                $newExpireTime = strtotime('+12 month', $baseTime);
                break;
            default:
                $newExpireTime = 0;
        }

        return $newExpireTime;
    }

    /**
     * 判断是否是vip
     *
     * @param $vipExpireTime
     * @return int 1:是 0:不是
     */
    public static function checkIsVip($vipExpireTime)
    {
        # 判断是否vip
        if ($vipExpireTime >= strtotime(date('Ymd'))) {
            $is_vip = 1;
        } else {
            $is_vip = 0;
        }

        return $is_vip;
    }
}