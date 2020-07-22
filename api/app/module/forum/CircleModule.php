<?php
/**
 * 社区圈子功能模块
 */
namespace api\app\module\forum;

use think\Db;
use think\Log;
use think\Cache;
use think\Exception;
use api\app\module\BaseModule;

class CircleModule extends BaseModule
{
    /**
     * 获取圈子列表
     *   从缓存获取，如没有，则从数据库获取，并写入缓存
     * @author coase
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCircleList()
    {
        // 获取缓冲
        $forum_circle_json = Cache::get('forum_circle_json');
        if ($forum_circle_json) {
            return json_decode($forum_circle_json, true);
        }

        $result = Db::name('forum_circle')
            ->where('status', 1)
            ->field('id,icon,name,desciption')
            ->select();
        $data = [];
        foreach ($result as $item) {
            $data[$item['id']] = $item;
        }

        // 设置缓存
        Cache::set('forum_circle_json', json_encode($data));

        return $data;
    }
}