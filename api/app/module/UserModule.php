<?php
/**
 * 用户功能模块
 * Class UserModule
 * @package api\app\module
 */
namespace api\app\module;
use think\Db;
use think\Log;
use think\Exception;
use api\app\module\aliyun\AliyunOssModule;

class UserModule extends BaseModule
{
    /**
     * 获取相册全路径
     * @param $jsonStr
     * @return array
     */
    public static function formatAlbumFullUrl($jsonStr)
    {
        $aRet = [];
        $arr = json_decode($jsonStr, true);
        if (! $arr) {
            return $aRet;
        }

        foreach ($arr as $item) { // $item 即为object
            $material = Db::name('oss_material')->where('status',2)->where('object', $item)->find();
            if (! $material) {
                continue;
            }
            $aRet[] = [
                'object' => $material['object'],
                'full_url' => MaterialModule::getFullUrl($item)
            ];
        }

        return $aRet;
    }

    /**
     * 获取视频全路径
     * @param string $jsonStr
     * @param int $userId 对视频的属性用户
     * @return array
     */
    public static function formatVideoFullUrl($jsonStr, $userId = null)
    {
        $aRet = [];
        $arr = json_decode($jsonStr, true);
        if (! $arr) {
            return $aRet;
        }

        foreach ($arr as $item) { // $item 即为object
            $material = Db::name('oss_material')->where('status > 0')->where('object', $item)->find();
            if (! $material) {
                continue;
            }
            if ($userId) {
                // 是否点赞
                $liked = Db::name('user_like')->where(['user_id'=>$userId,'object_id'=>$material['id'],'table_name'=>'oss_material','status'=>1])->count();
                $tmp['is_like'] = !empty($liked) ? 1 : 0;
            }

            if ($material['mime_type'] == 'video') {
                $cover_img = MaterialModule::getFullUrl($material['video_cover_img']);
            }

            $tmp['id'] = $material['id'];
            $tmp['like_num'] = $material['like_num'];
            $tmp['look_num'] = $material['look_num'];
            $tmp['object']   = $material['object'];
            $tmp['full_url'] = MaterialModule::getFullUrl($material['object']);
            $tmp['cover_img'] = isset($cover_img) ? MaterialModule::getFullUrl($cover_img) : '';
            
            $aRet[] = $tmp;
            unset($tmp);
        }

        return $aRet;
    }

    /**
     * 设置用户与位置机器人数据
     * @param $userId
     * @param $robotId
     * @return bool|int|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function setVirtualRobot($userId, $robotId)
    {
        $userRow = Db::name('user')->find($userId);

        // 判断数据是否过期，如果过期，则删除，重新生成
        $existRow = Db::name('robot_pos')->where(['user_id' => $userId, 'robot_id' => $robotId])->find();

        if (empty($existRow['expire_time']) || $existRow['expire_time'] < time()) {
            // 分配位置给模拟位置机器人
            $res = self::getVicinityPos($userRow['longitude'], $userRow['latitude'], mt_rand(1, 3), 20);
            if ($res == false || empty($res['results'])) {
                return false;
            }
            $posid = array_rand($res['results'], 1);
            if (! isset($res['results'][$posid])) {
                return false;
            }

            $aInput = [
                'user_id' => $userId,
                'robot_id' => $robotId,
                'longitude' => $res['results'][$posid]['location']['lng'],
                'latitude' => $res['results'][$posid]['location']['lat'],
                'province_name' => $res['results'][$posid]['province'],
                'city_name' => $res['results'][$posid]['city'],
                'district_name' => $res['results'][$posid]['area'],
                'expire_time' => time() + 60*30, // 过期时间30分钟
            ];

            // 删除历史数据
            Db::name('robot_pos')->where(['user_id' => $userId, 'robot_id' => $robotId])->delete();

            return Db::name('robot_pos')->insert($aInput);
        }

        return false;
    }

    /**
     * 初始化模拟位置机器人数据
     * @param $userId
     * @param $num
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function initVirtualRobots($userId, $num = 20)
    {
        $userRow = Db::name('user')->find($userId);

        // 判断数据是否过期，如果过期，则删除，重新生成
        $existRow = Db::name('robot_pos')->where('user_id', $userId)->find();

        if (empty($existRow['expire_time']) || $existRow['expire_time'] < time()) {
            // 随机获取多个模拟位置的机器人id
            $whereSql = 'virtual_pos=1 AND daren_status=2';
            if ($userRow['sex'] == 1) {
                $whereSql .= ' AND sex = 2';
            } elseif ($userRow['sex'] == 2) {
                $whereSql .= ' AND sex = 1';
            }
            $aVirtualId = self::randomVirtualRobots($num, $whereSql);
            if (! $aVirtualId) {
                return false;
            }

            // 分配位置给模拟位置机器人
            if (! self::allotVirtualPos($aVirtualId, $userRow['id'], $userRow['longitude'], $userRow['latitude'])) {
                Log::write(sprintf('%s：分配位置给模拟位置机器人失败,aVirtualId:%s,user_id:%s', __METHOD__, var_export($aVirtualId, true), $userRow['id']),'error');

                return false;
            }
        }

        return true;
    }

    /**
     * 根据经纬度获取附近位置列表
     * @param $longitude
     * @param $latitude
     * @param int $page
     * @param int $pageSize
     * @return bool|mixed
     * @throws Exception
     */
    public static function getVicinityPos($longitude, $latitude, $page = 1, $pageSize = 10)
    {
        $position = cmf_get_option('position');

        $url = 'http://api.map.baidu.com/place/v2/search';
        $aParam = [
            'ak' => $position['baidu_web_key'], // 请求服务权限标识
            'location' => $latitude . ',' . $longitude, // 中心点坐标 经度和纬度用","分割
            'query' => '小区', // 查询关键字 不同关键字间以$符号分隔
            'tag' => '住宅区', // 检索分类偏好，与q组合进行检索，多个分类以","分隔
            'radius' => '40000', // 圆形区域检索半径，单位为米。(当半径过大，超过中心点所在城市边界时，会变为城市范围检索，检索范围为中心点所在城市）
            'output' => 'json',
            'coord_type' => 'gcj02ll',
            'ret_coordtype' => 'gcj02ll',
            'page_size' => $pageSize, // 单次召回POI数量，默认为10条记录，最大返回20条。多关键字检索时，返回的记录数为关键字个数*page_size。
            'page_num' => $page - 1 // 分页页码，默认为0,0代表第一页，1代表第二页，以此类推
        ];
        $url = $url . '?' . http_build_query($aParam);

        try {
            $result = file_get_contents($url);
            $aResult = json_decode($result, true);
            if (! isset($aResult['status']) || $aResult['status'] !== 0) {
                Log::write(sprintf('%s：调用百度地图获取附近位置失败：%s', __METHOD__, var_export($result,true)),'error');
                return false;
            }

            return $aResult;

        } catch (Exception $e) {
            Log::write(sprintf('%s：调用百度地图获取附近位置系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('调用百度地图获取附近位置系统异常:' . $e->getMessage());
        }

//        $url = 'https://restapi.amap.com/v3/place/around';
//        $aParam = [
//            'key' => '9d8ffe7a3f25db0aa3da49f2f52f4133', // 请求服务权限标识 todo 从后台配置读取
//            'location' => $longitude . ',' . $latitude, // 中心点坐标 经度和纬度用","分割
//            'keywords' => '小区', // 查询关键字 多个关键字用“|”分割
//            'radius' => '50000', // 查询半径 默认3000，取值范围:0-50000。规则：大于50000按默认值，单位：米
//            'offset' => $pageSize, // 每页条数
//            'page' => $page // 当前页码
//        ];
//        $url = $url . '?' . http_build_query($aParam);
//
//        try {
//            $result = file_get_contents($url);
//            $aResult = json_decode($result, true);
//            if (! isset($aResult['status']) || $aResult['status'] != 1) { // 获取成功
//                Log::write(sprintf('%s：调用高德地图获取附近位置失败：%s', __METHOD__, var_export($result,true)),'error');
//                return false;
//            }
//
//            return $aResult;
//
//        } catch (Exception $e) {
//            Log::write(sprintf('%s：调用高德地图获取附近位置系统异常：%s', __METHOD__, $e->getMessage()),'error');
//            throw new Exception('调用高德地图获取附近位置系统异常:' . $e->getMessage());
//        }
    }

    /**
     * 分配位置给虚拟位置机器人
     * @param $aRobotUid
     * @param $userId
     * @param $longitude
     * @param $latitude
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function allotVirtualPos($aRobotUid, $userId, $longitude, $latitude)
    {
        $time = time();
        $page = 1;
        $pageSize = 20;
        $needCount = count($aRobotUid); // 需要条数

        $res0 = self::getVicinityPos($longitude, $latitude, $page, $pageSize);
        if ($res0 == false) {
            return false;
        }
        $total = $res0['total']; // 总条数
        $hadPage = ceil($total / $pageSize);
        $list = $res0['results'];


        if ($hadPage < 1) {
            $list = $res0['results'];
        } elseif ($hadPage < 5) {
            for ($page = 2; $page < $hadPage; $page = $page+2) {
                $res = self::getVicinityPos($longitude, $latitude, $page, $pageSize);
                if (is_array($res['results'])) {
                    $list = array_merge($list, $res['results']);
                }
            }
        } elseif ($hadPage < 10) {
            for ($page = 2; $page < $hadPage; $page = $page+3) {
                $res = self::getVicinityPos($longitude, $latitude, $page, $pageSize);
                if (is_array($res['results'])) {
                    $list = array_merge($list, $res['results']);
                }
            }
        } elseif ($hadPage < 15) {
            for ($page = 2; $page < $hadPage; $page = $page+4) {
                $res = self::getVicinityPos($longitude, $latitude, $page, $pageSize);
                if (is_array($res['results'])) {
                    $list = array_merge($list, $res['results']);
                }
            }
        } else {
            for ($page = 2; $page < $hadPage; $page = $page+5) {
                $res = self::getVicinityPos($longitude, $latitude, $page, $pageSize);
                if (is_array($res['results'])) {
                    $list = array_merge($list, $res['results']);
                }
            }
        }

        if (empty($list)) {
            Log::write(sprintf('%s：调用高德地图获取附近位置列表为空', __METHOD__),'error');
            return false;
        }

        $posidList = array_rand($list, $needCount);

        $aInput = [];

        if (!is_array($posidList)) {
            $posidList = [$posidList];
        }
        foreach ($posidList as $posid) {
            if (empty($aRobotUid)) {
                break;
            }
            $robotId = array_shift($aRobotUid);
            $aInput[] = [
                'user_id' => $userId,
                'robot_id' => $robotId,
                'longitude' => $list[$posid]['location']['lng'],
                'latitude' => $list[$posid]['location']['lat'],
                'province_name' => $list[$posid]['province'],
                'city_name' => $list[$posid]['city'],
                'district_name' => $list[$posid]['area'],
                'expire_time' => $time + 60*3, // 过期时间3分钟
            ];
        }

        // 删除历史数据
        Db::name('robot_pos')->where('user_id', $userId)->delete();

        // 写入新数据
        return Db::name('robot_pos')->insertAll($aInput);
    }

    /**
     * 随机获取多个模拟位置用户id
     * @param $num
     * @param string $whereSql
     * @return array
     */
    public static function randomVirtualRobots($num, $whereSql = 'virtual_pos=1')
    {
        $ids = [];
        $sql = "SELECT id FROM `t_user` 
                WHERE id >= (SELECT floor( RAND() * ((SELECT MAX(id) FROM `t_user` WHERE {$whereSql})
                                                     -(SELECT MIN(id) FROM `t_user` WHERE {$whereSql})) 
                                                  + (SELECT MIN(id) FROM `t_user` WHERE {$whereSql})))  
                      AND {$whereSql}
                ORDER BY id LIMIT {$num}";

        $result = Db::query($sql);
        if (empty($result)) {
            return $ids;
        }
        foreach ($result as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    /**
     * 获取用户财富等级
     * @param $userId
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserLevelByUid($userId)
    {
        $userFind = Db::name('user')->where('id', $userId)->field('coin,frozen_coin,used_coin')->find();
        $totalCoin = $userFind['coin'] + $userFind['frozen_coin'] + $userFind['used_coin'];

        return self::userLevelGrade($totalCoin);
    }

    /**
     * 财富等级
     * @param $coin
     * @return int|string
     */
    public static function userLevelGrade($coin)
    {
        // 默认值
        $gradeList = [
            0 => 0, // 小于等于0就是0级
            1 => 100, // 小于等于100就是1级
            2 => 500, // 小于500就是2级 ...
            3 => 1000, // 小于1000就是3级 ...
            4 => 2000,
            5 => 5000,
            6 => 10000,
            7 => 20000,
            8 => 50000,
            9 => 100000,
            10 => 200000,
            11 => 500000,
            12 => 1000000,
            13 => 2000000,
            14 => 5000000,
            15 => 1000000000,
        ];

        // 从设置中取
        $userLevelSetting = cmf_get_option('user_level_setting');
        if ($userLevelSetting) {
            $gradeList = $userLevelSetting['list'];
        }
        $level = 100; // 等级最大值
        foreach ($gradeList as $id => $value) {
            if ($coin <= $value) {
                $level = $id;
                break;
            }
        }

        return $level;
    }
}