<?php
/**
 * 修改用户在线状态
 */
namespace app\admin\service\commandir;

use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;

class ChangeOnlineStatusService extends BaseService
{
    /**
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function changeStatus()
    {
        $time = time();

        // 最后一次更新距离当前超过30分钟，离线
        Db::name('user_token')
            ->where('last_online_time', '<', $time - 1800)
            ->update(['online_status' => 0]);

        // 最后一次更新距离当前在30分钟内，在线
        Db::name('user_token')
            ->where('last_online_time', '>=', $time - 1800)
            ->where('online_status', '<', 5)
            ->update(['online_status' => 1]);

        return true;
    }

}