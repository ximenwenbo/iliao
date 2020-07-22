<?php
/**
 * 礼物功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;

class GiftModule extends BaseModule
{
    /**
     * 发送礼物
     *
     * @param int $sendUid 礼物发送者id
     * @param int $receiveUid 礼物接收者id
     * @param string $giftUniCode 礼物唯一码
     * @param int $giftNum 礼物数量，默认1
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function sendGift($sendUid, $receiveUid, $giftUniCode, $giftNum = 1)
    {
        $time = time();
        $giftRow = Db::name('gift')->where('uni_code', $giftUniCode)->find();
        if ($giftRow['coin'] <= 0) {
            self::exceptionError('礼物数据有误');
            return false;
        }

        // 礼物价值总金币
        $totalCoin = $giftRow['coin'] * $giftNum;

        # 礼物赠送订单
        $addOrder = [
            'order_no' => self::createOrderNo(),
            'send_uid' => $sendUid,
            'receive_uid' => $receiveUid,
            'gift_uni_code' => $giftUniCode,
            'num' => $giftNum,
            'coin' => $giftRow['coin'],
            'total_coin' => $totalCoin,
            'send_time' => $time,
            'receive_time' => $time,
            'exchange_status' => 2, // 默认直接兑换成金币
            'exchange_time' => $time
        ];

        // 启动事务
        Db::startTrans();
        try {
            # 获取发送者的用户数据，判断发送者的金币是否足够支付礼物 加锁
            $sendUserRow = Db::name('user')->lock(true)->find($sendUid);
            if ($sendUserRow['coin'] < $totalCoin) {
                // 回滚事务
                Db::rollback();
                self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', 201);
                return false;
            }

            # 创建礼物赠送订单
            $orderId = Db::name('gift_given_order')->insertGetId($addOrder);

            # 更新发送者用户金币（减扣金币）
            $updUser = [
                'coin' => Db::raw('coin-' . $totalCoin),
                'used_coin' => Db::raw('used_coin+' . $totalCoin),
            ];
            Db::name('user')->where('id', $sendUid)->update($updUser);

            # 新增发送者金币变更记录
            $insertCoin = [
                'user_id' => $sendUid,
                'change_type' => 2, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 3, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 32, // 32:送礼物支付
                'change_coin' => $totalCoin,
                'coin' => Db::name('user')->where('id', $sendUid)->value('coin'),
                'change_data_id' => $orderId,
                'change_subject' => '支付礼物',
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertCoin);

            # 更新接收者 用户金币（增加金币）
            $updAcceptUser = [
                'withdraw_coin' => Db::raw('withdraw_coin+' . $totalCoin),
            ];
            Db::name('user')->where('id', $receiveUid)->update($updAcceptUser);

            # 新增礼物接收者金币变更记录
            $insertAcceptCoin = [
                'user_id' => $receiveUid,
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 1, // 1:可提现 2:不可提现
                'class_id' => 4, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 42, // 42:收礼物收入
                'change_coin' => $totalCoin,
                'coin' => Db::name('user')->where('id', $receiveUid)->value('withdraw_coin'),
                'change_data_id' => $orderId,
                'change_subject' => '收入礼物',
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertAcceptCoin);

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new Exception('数据库执行异常,' . $e->getMessage(), 9901);
        }

        return true;
    }

    /**
     * 生成订单编号
     * @return string
     */
    private static function createOrderNo()
    {
        $timearr = @gettimeofday();
        return @date('YmdHis',$timearr['sec']).intval(substr($timearr['usec'],0,6)).mt_rand(1000,9999).mt_rand(1000,9999);
    }

}