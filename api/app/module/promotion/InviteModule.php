<?php
/**
 * 用户邀请功能模块
 */
namespace api\app\module\promotion;

use think\Db;
use think\Log;
use think\Exception;
use api\app\module\BaseModule;

class InviteModule extends BaseModule
{
    /**
     * 根据用户id获取他的专属业务员
     * @param $userId
     * @return bool|mixed
     */
    public static function getInvitedUidByUId($userId)
    {
        if (empty($userId)) {
            return false;
        }

        return Db::name('prom_invite_rela')->where('user_id', $userId)->value('parent_uid');
    }

    /**
     * 获取邀请层级
     * @param $userId
     * @param int $level
     * @return int
     */
    public static function getInvitedLevel($userId, $level = 1)
    {
        $fromUid = Db::name('user')->where('id', $userId)->value('from_uid');
        if ($fromUid) {
            return self::getInvitedLevel($fromUid, $level+1);
        }

        return $level;
    }

    /**
     * 邀请注册奖励
     * @param int $userId 新注册用户id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function inviteUserBonusByNewUid($userId)
    {
        $changeClassId = 11; // 邀请注册奖励id

        $divideInto = cmf_get_option('divide_into');
        $registerL1Coin = isset($divideInto['InviteUsers']['one']) ? intval($divideInto['InviteUsers']['one']) : 0;
        $registerL2Coin = isset($divideInto['InviteUsers']['two']) ? intval($divideInto['InviteUsers']['two']) : 0;

        if (empty($registerL1Coin) && empty($registerL2Coin)) {
            return false;
        }

        $relaFind = Db::name('prom_invite_rela')->where('user_id', $userId)->find();
        if (! $relaFind) {
            return true;
        }

        if (Db::name('prom_invite_bonus')->where(['from_uid'=>$userId, 'change_class_id'=>$changeClassId])->count()) {
            Log::write(sprintf('%s：邀请注册奖励错误：%s用户的推广者已经发过奖励', __METHOD__, $userId),'error');
            return false;
        }

        if ($relaFind['level'] == 1) {
            $bonusL1 = $registerL1Coin; // 奖励金币

            // 启动事务
            Db::startTrans();
            try {
                Db::name('user')->where('id', $relaFind['parent_uid'])->setInc('bonus_coin', $bonusL1);

                Db::name('prom_invite_bonus')->insert([
                    'user_id' => $relaFind['parent_uid'],
                    'from_uid' => $userId,
                    'invite_level' => 1,
                    'change_class_id' => $changeClassId,
                    'change_data_id' => 0,
                    'coin' => $bonusL1
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
            $bonusL1 = $registerL1Coin; // 上一级奖励金币
            $bonusL2 = $registerL2Coin; // 上二级奖励金币

            $rela2Find = Db::name('prom_invite_rela')->where('user_id', $relaFind['parent_uid'])->find();
            if (! $rela2Find) {
                Log::write(sprintf('%s：邀请注册奖励错误：%s上上级邀请关系数据不存在', __METHOD__, $userId),'error');

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
                        'from_uid' => $userId,
                        'invite_level' => 1,
                        'change_class_id' => $changeClassId,
                        'change_data_id' => 0,
                        'coin' => $bonusL1
                    ],
                    [
                        'user_id' => $rela2Find['parent_uid'],
                        'from_uid' => $userId,
                        'invite_level' => 2,
                        'change_class_id' => $changeClassId,
                        'change_data_id' => 0,
                        'coin' => $bonusL2
                    ]
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