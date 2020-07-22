<?php
/**
 * 用户设置功能模块
 */
namespace api\app\module;

use think\Db;
use think\Log;
use think\Exception;

class UserSettingModule extends BaseModule
{
    /**
     * 初始化用户设置数据
     * @param $userId
     * @return int|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function initUserSetting($userId)
    {
        $aInsert = [
            'user_id' => $userId,
            'open_video' => 1,
            'open_speech' => 1,
            'video_cost' => 0,
            'speech_cost' => 0,
        ];
        if (! Db::name("user_setting")->where('user_id', $userId)->count()) {
            $aInsert['user_id'] = $userId;
            return Db::name("user_setting")->insert($aInsert);
        } else {
            return Db::name("user_setting")->where('user_id', $userId)->update($aInsert);
        }
    }

}