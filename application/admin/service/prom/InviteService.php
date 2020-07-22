<?php
/**
 * 邀请奖励类
 */
namespace app\admin\service\prom;

use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;

class InviteService extends BaseService
{
    /**
     * 批量分配奖金
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function batchAssignBonus()
    {
        $divideInto = cmf_get_option('divide_into');

        $manL1Rate = isset($divideInto['RechargeShare']['one']) ? sprintf('%.2f',$divideInto['RechargeShare']['one'] / 100) : 0;
        $manL2Rate = isset($divideInto['RechargeShare']['two']) ? sprintf('%.2f',$divideInto['RechargeShare']['two'] / 100) : 0;
        $womanL1Rate = isset($divideInto['AnchorSplit']['one']) ? sprintf('%.2f',$divideInto['AnchorSplit']['one'] / 100) : 0;
        $womanL2Rate = isset($divideInto['AnchorSplit']['two']) ? sprintf('%.2f',$divideInto['AnchorSplit']['two'] / 100) : 0;

        // 频率控制
        if (! self::sendFrequenceCrontol()) {
            return false;
        }

        $recordSelect = Db::name('user_coin_record')
            ->whereIn('change_class_id', [1,41,42,43,44])
            ->where('prom_status', 0)
            ->limit(100)
            ->select()
            ->toArray();

        foreach ($recordSelect as $recordRow) {
            if ($recordRow['change_class_id'] == 1) { // 充值
                $res = self::assignBonus($recordRow, $manL1Rate, $manL2Rate);
            } else {
                $res = self::assignBonus($recordRow, $womanL1Rate, $womanL2Rate);
            }
        }

        return true;
    }

    /**
     * 发送频率控制
     * @return bool
     */
    public static function sendFrequenceCrontol()
    {
        return true;
    }

    /**
     * 分配奖励
     * @param $recordRow
     * @param $l1Rate
     * @param $l2Rate
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private static function assignBonus($recordRow, $l1Rate, $l2Rate)
    {
        $time = time();

        $relaFind = Db::name('prom_invite_rela')->where('user_id', $recordRow['user_id'])->find();
        if (! $relaFind) {
            Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                'prom_status' => 1,
                'prom_time' => $time
            ]);

            return true;
        }

        if ($relaFind['level'] == 1) {
            $bonusL1 = ceil($recordRow['change_coin'] * $l1Rate); // 奖励金币，舍入为最接近的整数
            if ($bonusL1 < 0) {
                Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                    'prom_status' => 10,
                    'prom_error' => '错误：奖励金币,$bonusL1:' . var_export($bonusL1, true),
                    'prom_time' => $time
                ]);

                return false;
            }

            // 启动事务
            Db::startTrans();
            try {
                Db::name('user')->where('id', $relaFind['parent_uid'])->setInc('bonus_coin', $bonusL1);

                Db::name('prom_invite_bonus')->insert([
                    'user_id' => $relaFind['parent_uid'],
                    'from_uid' => $recordRow['user_id'],
                    'invite_level' => 1,
                    'change_class_id' => $recordRow['change_class_id'],
                    'change_data_id' => $recordRow['change_data_id'],
                    'coin' => $bonusL1
                ]);

                Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                    'prom_status' => 2,
                    'prom_time' => $time
                ]);

                // 提交事务
                Db::commit();
                return true;

            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();

                Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            }

        } else {
            $bonusL1 = ceil($recordRow['change_coin'] * $l1Rate); // 上一级奖励金币
            $bonusL2 = ceil($recordRow['change_coin'] * $l2Rate); // 上二级奖励金币

            if ($bonusL1 < 0 || $bonusL2 < 0) {
                Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                    'prom_status' => 10,
                    'prom_error' => '错误：奖励金币,$bonusL1:' . var_export($bonusL1, true) . ', $bonusL2:' . var_export($bonusL2, true),
                    'prom_time' => $time
                ]);

                return false;
            }

            $rela2Find = Db::name('prom_invite_rela')->where('user_id', $relaFind['parent_uid'])->find();
            if (! $rela2Find) {
                Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                    'prom_status' => 10,
                    'prom_error' => '错误：上上级邀请关系数据不存在, $recordRow:' . var_export($recordRow, true),
                    'prom_time' => $time
                ]);

                return false;
            }

            // 启动事务
            Db::startTrans();
            try {
                Db::name('user')->where('id', $relaFind['parent_uid'])->setInc('bonus_coin', $bonusL1);

                Db::name('user')->where('id', $rela2Find['parent_uid'])->setInc('bonus_coin', $bonusL2);

                Db::name('prom_invite_bonus')->insertAll([
                    [
                        'user_id' => $relaFind['parent_uid'],
                        'from_uid' => $recordRow['user_id'],
                        'invite_level' => 1,
                        'change_class_id' => $recordRow['change_class_id'],
                        'change_data_id' => $recordRow['change_data_id'],
                        'coin' => $bonusL1
                    ],
                    [
                        'user_id' => $rela2Find['parent_uid'],
                        'from_uid' => $recordRow['user_id'],
                        'invite_level' => 2,
                        'change_class_id' => $recordRow['change_class_id'],
                        'change_data_id' => $recordRow['change_data_id'],
                        'coin' => $bonusL2
                    ]
                ]);

                Db::name('user_coin_record')->where('id', $recordRow['id'])->update([
                    'prom_status' => 2,
                    'prom_time' => $time
                ]);

                // 提交事务
                Db::commit();
                return true;

            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            }
        }

        return false;
    }

}