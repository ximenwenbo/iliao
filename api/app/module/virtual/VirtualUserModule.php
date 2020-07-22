<?php
/**
 * 虚拟用户功能模块
 */
namespace api\app\module\virtual;

use think\Db;
use think\Log;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\BaseModule;
use api\app\module\UserModule;
use api\app\module\CommonModule;
use api\app\module\MaterialModule;
use api\app\module\aliyun\AliyunOssModule;

class VirtualUserModule extends BaseModule
{
    /**
     * 获取模拟位置用户数据
     *   随机获取模拟机器人
     *   给这部分机器人分配位置
     *   返回这部分机器人数据列表
     * @param int $userId
     * @param int $num
     * @param bool $fresh 是否刷新模拟位置机器人
     * @return array|bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function getVirtualPosUser($userId, $yLevel, $num = 20, $fresh = false)
    {
        $userRow = Db::name('user')->find($userId);

        if ($fresh == 1) {
            // 更新模拟机器人
            if (UserModule::initVirtualRobots($userId, $num) == false) {
                return false;
            }
        }

        // 获取模拟位置机器人列表
        $result = Db::name('user')->alias('u')
            ->join('robot_pos r', 'r.robot_id = u.id')
            ->join('user_setting s', 's.user_id = u.id', 'LEFT')
            ->where('r.user_id', $userId)
            ->where('u.daren_status', 2)
            ->where('u.y_level <= ' . $yLevel)
            ->field('u.*,s.video_cost,s.speech_cost,r.longitude,r.latitude,r.province_name province,r.city_name city')
            ->select();
        if (! $result) {
            return [];
        }

        $aRet = [];
        foreach ($result as $row) {
            $distance = \dctool\Fun::calc_distance($userRow['longitude'], $userRow['latitude'], $row['longitude'], $row['latitude'], 1);
            $aRet[] = [
                'user_id' => $row['id'],
                'user_nickname' => $row['user_nickname'],
                'signature' => $row['signature'],
                'sex' => $row['sex'],
                'age' => $row['age'],
                'province_name' => $row['province'],
                'city_name' => $row['city'],
                'district_name' => '',
                'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                'speech_cost' => isset($row['speech_cost']) ? $row['speech_cost'] : 0,
                'video_cost' => isset($row['video_cost']) ? $row['video_cost'] : 0,
                'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                'distance' => CommonModule::convertDistance($distance),
                'longitude' => $row['longitude'],
                'latitude' => $row['latitude'],
                'online_state' => 1 // 机器人均为在线状态
            ];
        }
        return $aRet;
    }

}