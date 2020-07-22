<?php
/**
 * 用户看过功能模块
 */
namespace api\app\module;

use think\Db;

class LookModule extends BaseModule
{
    /**
     * 添加看过记录
     *
     * @param $userId
     * @param $beUserId
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function addLook($userId, $beUserId)
    {
        if ($userId == $beUserId) {
            return false;
        }

        if (Db::name("user_look")->where(['user_id' => $userId, 'be_user_id' => $beUserId])->count()) {
            return Db::name("user_look")
                ->where(['user_id' => $userId, 'be_user_id' => $beUserId])
                ->update(['last_look_time' => time()]);
        } else {
            Db::name("user")->where('id', $beUserId)->setInc('be_look_num');

            return Db::name("user_look")->insert([
                'user_id' => $userId,
                'be_user_id' => $beUserId,
                'last_look_time' => time(),
            ]);
        }
    }
}