<?php
/**
 * 聊天功能脚本模块
 */
namespace api\app\module\job;

use think\Db;
use \think\Log;
use think\Exception;

class JobChatModule extends JobBaseModule
{
    /**
     * 聊天异常订单定时处理脚本入口
     * 1：找到出错的订单 （ 条件：t_chat_order.status=1 & 订单最后更新时间在120秒之前）
     * 2：
     * 3：如果该订单没有任务id
     *    调用关闭异常聊天订单方法，关闭订单
     * 4：如果该订单有任务id
     *    调用关闭异常聊天订单方法，关闭订单和任务
     */
    public static function chatOrder4Job()
    {
        $currTime = time();
        $errOrders = Db::name('chat_order')->where("status = 1 AND update_time+300 < {$currTime}")->limit(20)->select();
        if (empty($errOrders)) {
            // 没有异常的订单需要处理
            return true;
        }

        $successOrder = [];

        foreach ($errOrders as $errOrder) {
            try {
                $closeRes = self::closeErrorChatOrder($errOrder['order_no']);
                if ($closeRes == false) {
                    // 记录日志
                    Log::record(sprintf('脚本定时关闭异常聊天订单，订单：%s，关闭出错：%s', $errOrder['order_no'], self::$errMessage), 'error');

                    // todo 是否要对异常订单进行标注
                } else {
                    $successOrder[] = $errOrder['order_no'];
                }
            } catch (Exception $e) {
                // 记录日志
                Log::record(sprintf('脚本定时关闭异常聊天订单，订单：%s，关闭出错：%s', $errOrder['order_no'], $e->getMessage()), 'error');
            }
        }

        return count($successOrder);
    }

    /**
     * 结束异常聊天订单
     *
     * @param string $orderNo 订单号
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function closeErrorChatOrder($orderNo)
    {
        $currentTime = time();

        $orderRow = Db::name('chat_order')->where(['order_no'=>$orderNo,'status'=>1])->find();
        if (! $orderRow) {
            self::exceptionError('无效订单号', -2002);
            return false;
        }

        # 计算当前订单消耗总金币
        $totalCoin = $orderRow['duration'] * $orderRow['per_cost'];

        // 更新聊天订单数据
        $updOrder = [
            'cost' => Db::raw('duration*per_cost'), // 计算总收费 时长*单价
            'status' => 3, // 状态 1:进行中 2:正常结束 3:非正常结束
            'err_subject' => '聊天未正常关闭，由脚本处理',
            'finish_time' => $currentTime,
        ];
        // 更新求聊任务数据
        $updTask = [
            'status' => 6, // 状态 1:发布成功 2:接单成功 5:任务正常结束6:任务异常结束
            'err_subject' => '聊天未正常关闭，由脚本处理',
            'finish_time' => $currentTime,
            'is_lock' => 0
        ];

        // 如果该聊天不收费，则直接完成订单（如果有任务，则结束任务）
        if ($totalCoin == 0) {
            # 更新聊天订单
            Db::name('chat_order')->where('order_no', $orderNo)->update($updOrder);

            # 如果有任务，则结束任务
            if ($orderRow['task_id']) {
                Db::name('chat_task')->where('id', $orderRow['task_id'])->update($updTask);
            }

            return true;
        }

        // 检测发起者冻结金币是否够减扣的金币
        $frozenCoin = Db::name('user')->where('id', $orderRow['launch_uid'])->value('frozen_coin');
        if ($frozenCoin < $totalCoin) {
            self::exceptionError('聊天发起者的冻结金币不够该订单消耗的总金币', -1020);
            return false;
        }

        // 启动事务
        Db::startTrans();
        try {
            # 更新聊天订单
            Db::name('chat_order')->where('order_no', $orderNo)->update($updOrder);

            # 如果有任务，则结束任务
            if ($orderRow['task_id']) {
                Db::name('chat_task')->where('id', $orderRow['task_id'])->update($updTask);
            }

            # 更新聊天发起者用户金币（减扣金币，增加冻结金币）
            $updUser = [
                'frozen_coin' => Db::raw('frozen_coin-' . $totalCoin),
                'used_coin' => Db::raw('used_coin+' . $totalCoin),
            ];
            Db::name('user')->where('id', $orderRow['launch_uid'])->update($updUser);

            # 新增聊天发起者金币变更记录
            $insertCoin = [
                'user_id' => $orderRow['launch_uid'],
                'change_type' => 2, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 3, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 31, // 31:音视频聊天支付
                'change_coin' => $totalCoin,
                'coin' => Db::name('user')->where('id', $orderRow['launch_uid'])->value('coin'),
                'change_data_id' => $orderRow['id'],
                'change_subject' => '支付'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $currentTime
            ];
            Db::name('user_coin_record')->insert($insertCoin);


            # 更新 聊天接受者 用户金币（增加金币）
            $updAcceptUser = [
                'withdraw_coin' => Db::raw('withdraw_coin+' . $totalCoin),
            ];
            Db::name('user')->where('id', $orderRow['accept_uid'])->update($updAcceptUser);

            # 新增 聊天接受者 金币变更记录
            $insertAcceptCoin = [
                'user_id' => $orderRow['accept_uid'],
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 1, // 1:可提现 2:不可提现
                'class_id' => 4, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 41, // 41:音视频聊天收入
                'change_coin' => $totalCoin,
                'coin' => Db::name('user')->where('id', $orderRow['accept_uid'])->value('withdraw_coin'),
                'change_data_id' => $orderRow['id'],
                'change_subject' => '收入'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $currentTime
            ];
            Db::name('user_coin_record')->insert($insertAcceptCoin);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            self::exceptionError('系统异常,' . $e->getMessage(), -1099);
            return false;
        }

        return true;
    }

}