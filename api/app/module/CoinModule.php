<?php
/**
 * 金币功能模块
 */
namespace api\app\module;

use think\Db;

class CoinModule extends BaseModule
{
    /**
     * 申请提现
     *  1.检查用户可提现金币是否充足,若不够,则返回错误
     *  2.事务操作 新增提现申请记录,可提现金币减去提现金币,冻结可提现金币加上提现金币
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public static function applyWithdraw($userId, $data)
    {
        $withdraw_account = $data['withdraw_account'];
        $coin = $data['coin'];
        $type = $data['type'];
        $amount = ConfigModule::coin2money($coin); //将金币转换成钱，单位分

        if ($type == 1) {
            // 提现可提现余额，需要扣手续费
            $publicConfig = cmf_get_option('public_config');
            $rate = $publicConfig['public_config']['Withdraw']['rate'];
            $handingFee = $amount * $rate / 100; // 手续费
            $paymentAmount = $amount - $handingFee; // 支付给用户的钱
        } else {
            // 提现邀请奖励，不需要口手续费
            $handingFee = 0;
            $paymentAmount = $amount;
        }

        // 启动事务
        Db::startTrans();
        $time = time();
        try {
            $userRow = Db::name('user')->lock(true)->find($userId);
            if (! $userRow) {
                Db::rollback();
                self::exceptionError(sprintf('没有找到该用户数据:%d', $userId), -1010);
                return false;
            }

            if ($type == 1) {
                // 可提现余额提现
                if ($userRow['withdraw_coin'] < $coin) {
                    Db::rollback();
                    self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', -1011);
                    return false;
                }

                //更新用户金币数据
                $updUser = [
                    'withdraw_coin' => Db::raw('withdraw_coin-' . $coin),
                    'withdraw_frozen_coin' => Db::raw('withdraw_frozen_coin+' . $coin),
                ];
                $result1 = Db::name('user')->where('id', $userId)->update($updUser);

            } else {
                // 邀请奖励提现
                if ($userRow['bonus_coin'] < $coin) {
                    Db::rollback();
                    self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', -1011);
                    return false;
                }

                //更新用户金币数据
                $updUser = [
                    'bonus_coin' => Db::raw('bonus_coin-' . $coin),
                    'bonus_frozen_coin' => Db::raw('bonus_frozen_coin+' . $coin),
                ];
                $result1 = Db::name('user')->where('id', $userId)->update($updUser);
            }

            //新增提现申请订单
            $insWithdrawOrder = [
                'user_id' => $userId,
                'order_no' => self::createOrderNo(),
                'coin' => $coin,
                'amount' => $amount,
                'withdraw_account' => $withdraw_account,
                'type' => $type,
                'handing_fee' => $handingFee,
                'payment_amount' => $paymentAmount,
                'status' => 1, // 状态：0:默认 1:审批中 2:审批通过打款中 3:已打款完成 10:审批拒绝
                'create_time' => $time,
            ];
            $result2 = Db::name('withdraw_order')->insert($insWithdrawOrder);

            if ($result1 && $result2) {
                Db::commit();
                return $insWithdrawOrder['order_no'];
            }

            Db::rollback();
            return false;

        } catch (\Exception $e) {
            self::exceptionError(sprintf('系统错误: errmsg %s, errcode %s', $e->getMessage(), $e->getCode()), -9999);

            Db::rollback();
            return false;
        }
    }

    /**
     * 生成订单编号
     * @return string
     */
    public static function createOrderNo()
    {
        $timearr = @gettimeofday();
        return @date('YmdHis',$timearr['sec']).intval(substr($timearr['usec'],0,6)).mt_rand(1000,9999).mt_rand(1000,9999);
    }
}