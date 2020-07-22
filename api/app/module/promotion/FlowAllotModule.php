<?php
/**
 * 流量分配功能模块
 */
namespace api\app\module\promotion;

use think\Db;
use think\Log;
use think\Exception;
use api\app\module\BaseModule;

class FlowAllotModule extends BaseModule
{
    /**
     * 给商户分配用户
     * @param $userId
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function allotUser2Merchant($userId)
    {
        if (Db::name('merchant_allot_user')->where('user_id', $userId)->count()) {
            return true;
        }

        if ($merchantId = self::getMerchantIdByConfig()) {
            $aInsert = [
                'merchant_id' => $merchantId,
                'user_id' => $userId,
                'create_time' => time()
            ];
            if (Db::name('merchant_allot_user')->insert($aInsert)) {
                return $merchantId;
            }
        }

        return false;
    }

    /**
     * 根据配置获取商户id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMerchantIdByConfig()
    {
        $flowSelect = Db::name('merchant_flow_config')
            ->where('ratio_value>0 AND total_value>0 AND delete_time=0')
            ->field('merchant_id,ratio_value')
            ->select()
            ->toArray();

        if (empty($flowSelect)) {
            return false;
        }

        foreach ($flowSelect as $v) {
            for ($i = 1; $i <= $v['ratio_value']; $i++) {
                $return[] = $v['merchant_id'];
            }
        }

        shuffle($return);
        return $return[array_rand($return)];
    }
}