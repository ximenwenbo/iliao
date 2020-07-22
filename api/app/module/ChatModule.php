<?php
/**
 * 聊天功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;

class ChatModule extends BaseModule
{
    /**
     * 发起聊天
     *
     * @param int $launchUid 发起者uid
     * @param int $acceptUid 接受者uid
     * @param int $robotUid 机器人uid
     * @param int $type 聊天类型（1:文字 2:语音 3:视频）
     * @return array|bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function launchChat($launchUid, $acceptUid, $type, $robotUid = 0)
    {
        $launchUserRow = Db::name('user')->where('id', $launchUid)->find();

        if ($robotUid) {
            // 如果是机器人，则取机器人的收费配置
            $acceptSettingRow = Db::name('user_setting')->where('user_id', $robotUid)->find();
        } else {
            $acceptSettingRow = Db::name('user_setting')->where('user_id', $acceptUid)->find();
        }

        if ($type == 2) {
            // 语音聊天
            $perCost = isset($acceptSettingRow['speech_cost']) ? $acceptSettingRow['speech_cost'] : 0; // 每分钟收费
            $homeId = self::createNewHomeId($launchUid, $acceptUid);
        } elseif ($type == 3) {
            // 视频聊天
            $perCost = isset($acceptSettingRow['video_cost']) ? $acceptSettingRow['video_cost'] : 0; // 每分钟收费
            $homeId = self::createNewHomeId($launchUid, $acceptUid);
        } else {
            // 文字聊天不收费
            $perCost = 0; // 每分钟收费
        }

        # 判断发起者的金币是否足以支付1分钟的花费
        if ($launchUserRow['coin'] < $perCost * 1) {
            self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', 201);
            return false;
        }

        # 计算发起者的金币能聊几分钟
        if ($perCost > 0) {
            $restTime = floor(($launchUserRow['coin']) / $perCost);
        } else {
            $restTime = -1; //没有设置的话，就无限时间
        }

        # 创建聊天订单
        $addOrder = [
            'order_no'   => self::createOrderNo(),
            'home_id'    => isset($homeId) ? $homeId : 0,
            'type'       => $type,
            'launch_uid' => $launchUid,
            'accept_uid' => $acceptUid,
            'robot_uid'  => $robotUid,
            'duration'   => 0, // 时长，单位分
            'per_cost'   => $perCost,
            'create_time'=> time(),
            'update_time'=> time(),
        ];
        if (Db::name('chat_order')->insert($addOrder)) {
            return [
                'order_no'  => $addOrder['order_no'],
                'home_id'   => isset($homeId) ? $homeId : 0,
                'rest_time' => $restTime
            ];
        }

        self::exceptionError('创建聊天订单失败');
        return false;
    }

    /**
     * 更新聊天时长，每分钟更新一次
     *   检测聊天订单数据是否合法
     *   检测发起者金币是否满足一个单位时间的费用
     *   更新订单时间 和 金币花费
     *   更新发起者金币余额
     *
     * @param string $orderNo 订单号
     * @param int $durationTime 持续时间
     * @return bool|float|int
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function updChatDurationPerMin($orderNo, $durationTime)
    {
        $time = time();

        $orderRow = Db::name('chat_order')->where('order_no', $orderNo)->find();
        if (! $orderRow) {
            self::exceptionError('订单号非法', -1001);
            return false;
        }

        if ($orderRow['status'] > 1) {
            self::exceptionError('该订单已经关闭了', -1003);
            return false;
        }

        $truncationTime = $durationTime - $orderRow['duration'];// 距离上次更新的时间差
        $truncationCost = $truncationTime * $orderRow['per_cost']; //本次减扣费用

//        if ($durationTime > 1 && $time-$orderRow['update_time'] < 60) {
//            // 记录错误日志
//            Log::write(sprintf('%s，更新聊天时长接口，该订单最后更新时间距离现在不到1分钟：订单：%s', __METHOD__, $orderNo),'error');
//            self::exceptionError('最后更新时间距离现在不到1分钟', -1004);
//            return false;
//        }

//        if ($time-$orderRow['update_time'] > 120) {
//            // 记录错误日志
//            Log::write(sprintf('%s，更新聊天时长接口，该订单最后更新时间距离现在超过了2分钟：订单：%s', __METHOD__, $orderNo),'error');
//            self::exceptionError('最后更新时间距离现在超过了2分钟', -1005);
//            return false;
//        }

        if ($truncationTime < 1 || $truncationTime > 5) {
            // 记录错误日志
            Log::write(sprintf('%s，更新聊天时长接口，聊天持续时间和服务端出现异常：订单：%s，请求时长参数：%s', __METHOD__, $orderNo, $durationTime),'error');
            self::exceptionError('聊天持续时间和服务端不一致', -1006);
            return false;
        }

        // 如果订单加锁，则不能操作
        if ($orderRow['is_lock'] == 1) {
            self::exceptionError('该订单已加锁', -1000);
            return false;
        }

        // 每分钟收费
        $perCost = $orderRow['per_cost'];

        // 启动事务
        Db::startTrans();
        try {
            # 获取聊天发起者的用户数据，判断发起者的金币是否足以支付花费 加锁
            $launchUserRow = Db::name('user')->lock(true)->where('id', $orderRow['launch_uid'])->find();
            if ($launchUserRow['coin'] < $truncationCost) {
                // 回滚事务
                Db::rollback();
                self::exceptionError(\dctool\Cgf::getCoinNickname().'不足', -1011);
                return false;
            }

            # 更新订单表 时长
            $updOrder = [
                'duration'    => $durationTime, // 时长，单位分
                'status'      => 1,
                'update_time' => $time
            ];
            Db::name('chat_order')->where('order_no', $orderNo)->update($updOrder);

            if ($perCost > 0) { // 收费大于0时才执行下面的逻辑
                # 更新 聊天发起者 用户金币（增加冻结金币）
                $updUser = [
                    'coin' => Db::raw('coin-' . $truncationCost),
                    'frozen_coin' => Db::raw('frozen_coin+' . $truncationCost),
                ];
                Db::name('user')->where('id', $orderRow['launch_uid'])->update($updUser);
            }
            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new Exception('系统异常,' . $e->getMessage(), 9901);
        }

        # 剩余可用的时间 分钟
        if ($perCost == 0) {
            $restTime = -1; // 表示不限时间
        } else {
            $restTime = floor(($launchUserRow['coin'] - $perCost) / $perCost);
        }

        return $restTime;
    }

    /**
     * 正常结束聊天
     *
     * @param string $orderNo 订单号
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function closeChat($orderNo)
    {
        $time = time();

        $orderRow = Db::name('chat_order')->where('order_no', $orderNo)->find();
        if (! $orderRow) {
            self::exceptionError('订单号非法', -1002);
            return false;
        }

        if ($orderRow['status'] > 1) {
            self::exceptionError('该订单已经关闭了', -1003);
            return false;
        }

        if ($time - $orderRow['update_time'] > 120) {
            // 记录错误日志
            Log::write(sprintf('%s，结束聊天接口，该订单最后更新时间距离现在超过了2分钟：订单：%s', __METHOD__, $orderNo),'error');
            self::exceptionError('最后更新时间距离现在超过了2分钟', -1004);
            return false;
        }

        // 如果订单加锁，则不能操作
        if ($orderRow['is_lock'] == 1) {
            self::exceptionError('该订单已加锁', -1000);
            return false;
        }
        // 给订单加锁
        Db::name('chat_order')->where('order_no', $orderNo)->update(['is_lock' => 1]);

        # 计算当前订单消耗总金币
        $coin = $orderRow['duration'] * $orderRow['per_cost'];

        // 如果该聊天不收费，则直接完成订单（如果有任务，则结束任务）
        if ($coin == 0) {
            # 更新聊天订单
            $updOrder = [
                'cost' => 0, // 计算总收费 时长*单价
                'status' => 2, // 状态 1:进行中 2:正常结束 3:非正常结束
                'finish_time' => $time,
                'is_lock' => 0
            ];
            Db::name('chat_order')->where('order_no', $orderNo)->update($updOrder);

            # 如果有任务，则结束任务
            if ($orderRow['task_id']) {
                Db::name('chat_task')->where('id', $orderRow['task_id'])->update([
                    'status' => 5,
                    'finish_time' => $time,
                    'is_lock' => 0
                ]);
            }

            return true;
        }

        // 检测发起者冻结金币是否够减扣的金币
        $frozenCoin = Db::name('user')->where('id', $orderRow['launch_uid'])->value('frozen_coin');
        if ($frozenCoin < $coin) {
            Log::write(sprintf('%s，结束聊天接口，聊天发起者的冻结金币不够该订单消耗的总金币：订单：%s', __METHOD__, $orderNo),'error');
            self::exceptionError('聊天发起者的冻结金币不够该订单消耗的总金币', -1020);
            return false;
        }

        // 启动事务
        Db::startTrans();
        try {
            # 更新聊天订单
            $updOrder = [
                'cost' => Db::raw('duration*per_cost'), // 计算总收费 时长*单价
                'status' => 2, // 状态 1:进行中 2:正常结束 3:非正常结束
                'finish_time' => $time,
                'is_lock' => 0
            ];
            Db::name('chat_order')->where('order_no', $orderNo)->update($updOrder);

            # 如果有任务，则结束任务
            if ($orderRow['task_id']) {
                Db::name('chat_task')->where('id', $orderRow['task_id'])->update([
                    'status' => 5,
                    'finish_time' => $time,
                    'is_lock' => 0
                ]);
            }

            # 更新聊天发起者用户金币（减扣金币，增加冻结金币）
            $updUser = [
                'frozen_coin' => Db::raw('frozen_coin-' . $coin),
                'used_coin' => Db::raw('used_coin+' . $coin),
            ];
            Db::name('user')->where('id', $orderRow['launch_uid'])->update($updUser);

            # 新增聊天发起者金币变更记录
            $insertCoin = [
                'user_id' => $orderRow['launch_uid'],
                'change_type' => 2, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 3, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 31, // 31:音视频聊天支付
                'change_coin' => $coin,
                'coin' => Db::name('user')->where('id', $orderRow['launch_uid'])->value('coin'),
                'change_data_id' => $orderRow['id'],
                'change_subject' => '支付'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertCoin);


            # 更新 聊天接受者 用户金币（增加金币）
            $updAcceptUser = [
                'withdraw_coin' => Db::raw('withdraw_coin+' . $coin),
            ];
            Db::name('user')->where('id', $orderRow['accept_uid'])->update($updAcceptUser);

            # 新增 聊天接受者 金币变更记录
            $insertAcceptCoin = [
                'user_id' => $orderRow['accept_uid'],
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 1, // 1:可提现 2:不可提现
                'class_id' => 4, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 41, // 41:音视频聊天收入
                'change_coin' => $coin,
                'coin' => Db::name('user')->where('id', $orderRow['accept_uid'])->value('withdraw_coin'),
                'change_data_id' => $orderRow['id'],
                'change_subject' => '收入'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $time
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

    /**
     * 接求聊任务
     * @param $userId
     * @param $taskId
     * @return array|bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChatTask($userId, $taskId)
    {
        $taskRow = Db::name('chat_task')->find($taskId);

        $launchUserRow = Db::name('user')->where('id', $taskRow['user_id'])->find();

        # 判断发起者的金币是否足以支付一分钟的花费
        if ($launchUserRow['coin'] < $taskRow['per_cost']) {
            self::exceptionError('求聊者'.\dctool\Cgf::getCoinNickname().'不足', -1011);
            return false;
        }

        if ($taskRow['type'] > 1) {
            $homeId = self::createNewHomeId($taskRow['user_id'], $userId);
        }

        # 计算发起者的金币能聊几分钟
        if ($taskRow['per_cost'] > 0) {
            $restTime = floor(($launchUserRow['coin']) / $taskRow['per_cost']);
        } else {
            $restTime = -1; //没有设置，就无限时间
        }

        // 启动事务
        Db::startTrans();
        try {
            # 修改任务状态
            Db::name('chat_task')->where(['id'=>$taskId,'status'=>1,'is_lock'=>1])->update(['status'=>2]);

            # 创建聊天订单
            $addOrder = [
                'task_id'    => $taskRow['id'],
                'home_id'    => isset($homeId) ? $homeId : 0,
                'order_no'   => self::createOrderNo(),
                'type'       => $taskRow['type'],
                'launch_uid' => $taskRow['user_id'],
                'accept_uid' => $userId,
                'duration'   => 0, // 时长，单位分
                'per_cost'   => $taskRow['per_cost'],
                'create_time'=> time(),
                'update_time'=> time(),
            ];
            Db::name('chat_order')->insert($addOrder);

            // 提交事务
            Db::commit();

        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new Exception('系统异常,' . $e->getMessage(), -1099);
        }

        return [
            'order_no'  => $addOrder['order_no'],
            'home_id'   => isset($homeId) ? $homeId : 0,
            'rest_time' => $restTime
        ];
    }

    /**
     * 生成聊天订单编号
     * @return string
     */
    private static function createOrderNo()
    {
        $timearr = @gettimeofday();
        return @date('YmdHis',$timearr['sec']).intval(substr($timearr['usec'],0,6)).mt_rand(1000,9999).mt_rand(1000,9999);
    }

    /**
     * 计算距离当前多久前
     * @param $the_time
     * @return string
     */
    public static function timeTran($the_time)
    {
        $now_time  = time();
        $show_time = $the_time;
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return '刚刚';
        } else {
            if ($dur < 60) {
                return '刚刚';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return date('m-d', $the_time);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取新的房间id (腾讯云要求房间号必须在1~10,000,000之间)
     *
     * @param $launchUid
     * @param $acceptUid
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function createNewHomeId($launchUid, $acceptUid)
    {
        $autoId = Db::name('chat_home')->insertGetId([
            'create_time' => time(),
            'launch_uid' => $launchUid,
            'accept_uid' => $acceptUid
        ]);

        if ($autoId <= 10000000) {
            $homeId = $autoId;
        } else {
            $homeId = $autoId % 10000000;
        }

        Db::name('chat_home')->where('id', $autoId)->update(['home_id' => $homeId]);

        return intval($homeId);

//        $maxHomeId = Db::name('chat_home')->max('home_id');
//        if (! $maxHomeId) {
//            $defaultHomeId = 1;
//            Db::name('chat_home')->insert(['home_id' => $defaultHomeId, 'launch_uid' => $launchUid, 'accept_uid' => $acceptUid]);
//
//            return $defaultHomeId;
//        }
//
//        if ($maxHomeId + 1 < 10000000) {
//            Db::name('chat_home')->insert(['home_id' => $maxHomeId + 1, 'launch_uid' => $launchUid, 'accept_uid' => $acceptUid]);
//
//            return $maxHomeId + 1;
//        }
//
//        $tryHomeId = 1;
//        while (true) {
//            if (! Db::name('chat_home')->where('home_id', $tryHomeId)->count()) {
//                Db::name('chat_home')->insert(['home_id' => $tryHomeId, 'launch_uid' => $launchUid, 'accept_uid' => $acceptUid]);
//                return $tryHomeId;
//            }
//
//            $tryHomeId++;
//        }
    }

}