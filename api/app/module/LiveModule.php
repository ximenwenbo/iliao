<?php
/**
 * 直播功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use dctxyun\Common;
use think\Exception;
use dctxyun\Imapi;
use dctxyun\Liveapi;

class LiveModule extends BaseModule
{
    /**
     * 创建推流，并返回推流地址
     * @param $userId
     * @param $streamId
     * @param $aOption
     * @return string
     */
    public static function createPushStream($userId, $streamId, $aOption)
    {
        $insert = [
            'user_id' => $userId,
            'option_type' => $aOption['option_type'],
            'option_id' => $aOption['option_id'],
            'option_class_id' => $aOption['option_class_id'],
            'stream_id' => $streamId,
            'mix_stream_session_id' => $streamId
        ];

        $streamAutoId = Db::name('live_stream')->insertGetId($insert);

        return Common::getTLiveUrl($streamId, ['stream_auto_id' => $streamAutoId]);
    }

    /**
     * 收费直播
     *
     * @param string $orderNo 订单号
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function charge4costLive($userId, $liveId)
    {
        $time = time();

        $liveRow = Db::name('live_home')->find($liveId);
        if (! $liveRow) {
            self::exceptionError('直播间id非法', -1102);
            return false;
        }

        if ($liveRow['status'] > 1) {
            self::exceptionError('该直播间已经关闭了', -1103);
            return false;
        }

        # 需要消耗的金币
        $inCoin = $liveRow['in_coin'];

        // 检测发起者冻结金币是否够减扣的金币
        $userCoin = Db::name('user')->where('id', $userId)->value('coin');
        if ($userCoin < $inCoin) {
            Log::write(sprintf('%s，进入收费直播间接口，进入者的金币不够该直播间消耗的金币', __METHOD__),'log');
            self::exceptionError('进入者的'.\dctool\Cgf::getCoinNickname().'不够该直播间消耗的金币', -1120);
            return false;
        }

        // 启动事务
        Db::startTrans();
        try {
            # 新增进入收费直播间订单
            $addLiveOrder = [
                'order_no' => self::createOrderNo(),
                'user_id' => $userId,
                'live_id' => $liveId,
                'live_uid' => $liveRow['user_id'],
                'cost' => $inCoin,
                'status' => 1,
            ];
            $orderAutoId = Db::name('live_in_order')->insertGetId($addLiveOrder);

            # 更新聊天发起者用户金币（直接减扣金币）
            $updUser = [
                'coin' => Db::raw('coin-' . $inCoin),
                'used_coin' => Db::raw('used_coin+' . $inCoin),
            ];
            Db::name('user')->where('id', $userId)->update($updUser);

            # 新增观看者金币变更记录
            $insertCoin = [
                'user_id' => $userId,
                'change_type' => 2, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 3, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 33, // 33:进收费直播间门票支付
                'change_coin' => $inCoin,
                'coin' => Db::name('user')->where('id', $userId)->value('coin'),
                'change_data_id' => $orderAutoId,
                'change_subject' => '支付'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertCoin);


            # 更新 主播 用户金币（增加金币）
            $updLiveUser = [
                'withdraw_coin' => Db::raw('withdraw_coin+' . $inCoin),
            ];
            Db::name('user')->where('id', $liveRow['user_id'])->update($updLiveUser);

            # 新增 聊天接受者 金币变更记录
            $insertLiveCoin = [
                'user_id' => $liveRow['user_id'],
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 1, // 1:可提现 2:不可提现
                'class_id' => 4, // 类别 1:充值,2:提现 3:支付 4:收入
                'change_class_id' => 43, // 43:进收费直播间门票收入
                'change_coin' => $inCoin,
                'coin' => Db::name('user')->where('id', $liveRow['user_id'])->value('withdraw_coin'),
                'change_data_id' => $orderAutoId,
                'change_subject' => '收入'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $time
            ];
            Db::name('user_coin_record')->insert($insertLiveCoin);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            Log::write(sprintf('%s，进入收费直播间接口，系统异常：%s', __METHOD__, $e->getMessage()),'error');
            self::exceptionError('系统异常,' . $e->getMessage(), -1099);
            return false;
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

    /**
     * 判断是否直播间管理员
     * @param $userId
     * @param $liveUid
     * @return int
     */
    public static function checkIsManager($userId, $liveUid)
    {
        if (Db::name('live_manager_setting')->where(['user_id' => $userId, 'live_uid' => $liveUid])->count()) {
            return 1;
        }

        return 0;
    }

    /**
     * 判断是否直播间禁言
     * @param $userId
     * @param $liveUid
     * @param $liveId
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function checkIsBanspeech($userId, $liveUid, $liveId)
    {
        $banspeechFind = Db::name('live_banspeech_setting')->where(['user_id' => $userId, 'live_uid' => $liveUid])->find();

        if (empty($banspeechFind)) {
            return 0;
        }
        if ($banspeechFind['type'] == 2) {
            return  1;
        }
        if ($banspeechFind['type'] == 1 && $banspeechFind['live_id'] == $liveId) {
            return 1;
        }

        return 0;
    }

    /**
     * 主播关闭直播间
     * @param $liveFind
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function closeLiveHome($liveFind)
    {
        $time = time();

        $streamFind = Db::name('live_stream')
            ->where(['option_type' => 2, 'option_id' => $liveFind['id'], 'user_id' => $liveFind['user_id']])->find();

        // 启动事务
        Db::startTrans();
        try {
            Db::name('live_home')->where('id', $liveFind['id'])->update([
                'status' => 2,
                'end_time' => $time
            ]);

            Db::name('live_stream')->where('id', $streamFind['id'])->update([
                'status' => 2,
                'end_time' => $time
            ]);

            Db::name('live_home_viewer')->where(['live_id'=>$liveFind['id'], 'user_type'=>3])->delete();

            // 提交事务
            Db::commit();

        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            Log::write(sprintf('%s：数据库操作错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            return false;
        }

        // 销毁直播间群
        Imapi::destroyGroup($liveFind['user_id']);

        $aSlaveStream = Db::name('live_stream')
            ->where(['option_type' => 2, 'option_id' => $liveFind['id'], 'option_class_id' => 22, 'status' => 1])
            ->column('stream_id');
        if (!empty($aSlaveStream)) {
            // 取消混流
            Liveapi::cancelMixStream($streamFind['stream_id'], $streamFind['mix_stream_session_id']);
        }

        return true;
    }
}