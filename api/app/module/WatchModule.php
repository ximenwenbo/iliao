<?php
/**
 * 守护功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;

class WatchModule extends BaseModule
{
    /**
     * 判断是否是守护
     * @param $userId
     * @param $liveUserId
     * @return int 1:是 0:不是
     */
    public static function checkIsWatch($userId, $liveUserId)
    {
        $watchExpireTime = Db::name('watch_relation')
            ->where('user_id', $userId)
            ->where('live_user_id', $liveUserId)
            ->value('watch_expire_time');

        # 判断是否vip
        if ($watchExpireTime >= strtotime(date('Ymd'))) {
            $isWatch = 1;
        } else {
            $isWatch = 0;
        }

        return $isWatch;
    }

    /**
     * 获取vip收费列表
     * @return array
     */
    public static function getWatchTypeList($userId = 0)
    {
        $watchList = [
            '7' =>[
                'day_time' => 7,
                'coin' => 300,
                'subject' => '7天体验'
            ],
            '30' => [
                'day_time' => 30,
                'coin' => 1000,
                'subject' => '1个月'
            ],
            '365' => [
                'day_time' => 365,
                'coin' => 12000,
                'subject' => '尊贵守护全年'
            ],
        ];

        return $watchList;
    }

    /**
     * 新增守护
     * @param $sendUid
     * @param $receiveUid
     * @param $dTime
     * @return bool
     * @throws Exception
     */
    public static function addWatch($sendUid, $receiveUid, $dTime)
    {
        $time = time();

        // 礼物价值总金币
        $list = self::getWatchTypeList();
        if (empty($list[$dTime])) {
            self::exceptionError('时间参数错误');
            return false;
        }

        $coin = $list[$dTime]['coin'];
        $addTime = $list[$dTime]['day_time'] * 86400;

        # 礼物赠送订单
        $addOrder = [
            'order_no' => self::createOrderNo(),
            'send_uid' => $sendUid,
            'receive_uid' => $receiveUid,
            'day_time' => $dTime,
            'coin' => $coin,
        ];

        // 启动事务
        Db::startTrans();
        try {
            # 获取发送者的用户数据，判断发送者的金币是否足够支付礼物 加锁
            $sendUserRow = Db::name('user')->lock(true)->find($sendUid);
            if ($sendUserRow['coin'] < $coin) {
                // 回滚事务
                Db::rollback();
                self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', 201);
                return false;
            }

            # 创建守护订单
            $orderId = Db::name('watch_order')->insertGetId($addOrder);

            # 更新守护关系表
            if (Db::name('watch_relation')->where(['user_id' => $sendUid, 'live_user_id' => $receiveUid])->count()) {
                Db::name('watch_relation')->where(['user_id' => $sendUid, 'live_user_id' => $receiveUid])->setInc('watch_expire_time', $addTime);
            } else {
                Db::name('watch_relation')->insert([
                    'user_id' => $sendUid,
                    'live_user_id' => $receiveUid,
                    'watch_expire_time' => $time + $addTime,
                ]);
            }

            # 更新发送者用户金币（减扣金币）
            $updUser = [
                'coin' => Db::raw('coin-' . $coin),
                'used_coin' => Db::raw('used_coin+' . $coin),
            ];
            Db::name('user')->where('id', $sendUid)->update($updUser);

            # 新增发送者金币变更记录
            $insertCoin = [
                'user_id' => $sendUid,
                'change_type' => 2, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 3, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 34, // 34:送出守护支付
                'change_coin' => $coin,
                'coin' => Db::name('user')->where('id', $sendUid)->value('coin'),
                'change_data_id' => $orderId,
                'change_subject' => '送出守护',
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertCoin);

            # 更新接收者 用户金币（增加金币）
            $updAcceptUser = [
                'withdraw_coin' => Db::raw('withdraw_coin+' . $coin),
            ];
            Db::name('user')->where('id', $receiveUid)->update($updAcceptUser);

            # 新增礼物接收者金币变更记录
            $insertAcceptCoin = [
                'user_id' => $receiveUid,
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 1, // 1:可提现 2:不可提现
                'class_id' => 4, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 44, // 44:收到守护收入
                'change_coin' => $coin,
                'coin' => Db::name('user')->where('id', $receiveUid)->value('withdraw_coin'),
                'change_data_id' => $orderId,
                'change_subject' => '收入守护',
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