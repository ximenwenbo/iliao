<?php
/**
 * User: coase
 * Date: 2018-10-23
 * Time: 14:05
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\CommonModule;
use api\app\module\FandefModule;
use api\app\module\MaterialModule;

/**
 * #####首页列表模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 首页热门
 * 2. 获取用户基本信息
 * 3. 修改用户基本信息
 * ``````````````````
 */
class IndexController extends RestBaseController
{
    /**
     * 首页热门
     *   条件：达人，与登录用户性别相反
     *   排序：根据被查看数倒序
     */
    public function popularUser()
    {
        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            $userId = $this->userId;

            // 虚拟位置用户数据
            $virtualUsers = [];
            /*if ($iPage == 1 && $userId) {
                if (class_exists('\api\app\module\virtual\VirtualUserModule')) {
                    $virtualUsers = \api\app\module\virtual\VirtualUserModule::getVirtualPosUser($userId, $this->yLevel, mt_rand(14,18), true);
                    if (! $virtualUsers) {
                        $virtualUsers = [];
                    }
                }
            }*/

            // 性别筛选 取与登录用户性别相反的数据
            if ($this->userSex == 1) {
                $whereSex = 'u.sex = 2';
            } elseif ($this->userSex == 2) {
                $whereSex = 'u.sex = 1';
            } else {
                $whereSex = '';
            }

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            // 获取客服uid
            $customUids = Db::name('role_user')->where('role_id', 3)->column('user_id');

            $result = Db::name('user')->alias('u')
                ->join('user_setting s', 's.user_id = u.id', 'LEFT')
                ->join('user_token t', 't.user_id = u.id')
                ->whereIn('t.online_status', [1,2,3,4])
                ->where('u.user_type',2)
                ->where('u.virtual_pos',0)
                ->where('u.user_status',1)
                ->where('u.daren_status',2)
//                ->where('u.y_level <= '.$this->yLevel)
                ->whereNotIn('u.id', $aBlockedUid)
//                ->where($whereSex)
                ->whereNotIn('u.id', $customUids)
                ->field("u.*,(u.withdraw_coin+u.withdraw_frozen_coin+u.withdraw_used_coin) as total_coin,s.video_cost,s.speech_cost,t.online_status,
                case when (t.online_status=1) then 10
                     when (t.online_status=2) then 9
                     when (t.online_status=4) then 8
                     when (t.online_status=3) then 7
                     when (t.online_status=5) then 6
                else 0 end as sor1")
                ->order('sor1', 'desc')
                ->order('total_coin', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result as $row) {
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'signature' => $row['signature'],
                    'sex' => $row['sex'],
                    'age' => $row['age'],
                    'province_name' => $row['province_name'],
                    'city_name' => $row['city_name'],
                    'district_name' => $row['district_name'],
                    'show_photo' => FandefModule::getAvatarFullUrl($row['avatar'], true),
                    'speech_cost' => isset($row['speech_cost']) ? $row['speech_cost'] : 0,
                    'video_cost' => isset($row['video_cost']) ? $row['video_cost'] : 0,
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'distance' => '0',
                    'longitude' => 0,
                    'latitude' => 0,
                    'online_state' => $row['online_status']
                ];
            }

            // 获取banner
            $bannerResult = Db::name('banner')->where(['type' => 1, 'status' => 1])->order('sort')->select();
            $bannerList = [];
            if (! empty($bannerResult)) {
                foreach ($bannerResult as $banner) {
                    $bannerList[] = [
                        'img' => MaterialModule::getFullUrl($banner['img_url']),
                        'link' => $banner['a_url']
                    ];
                }
            }

            $this->success("OK", [
                'list' => array_merge($virtualUsers, $aRet),
                'banner' => $bannerList,
                'show_style' => 1
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 首页附近，根据距离倒序排列
     */
    public function vicinityUser()
    {
        try {
            $validate = new Validate([
                'longitude' => 'require',
                'latitude' => 'require',
                'page' => 'require|min:1'
            ]);

            $validate->message([
                'longitude.require' => '请输入经度',
                'latitude.require' => '请输入纬度',
                'page.require' => '请输入页码'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (empty($param['distance'])) {
                $param['distance'] = 1000000; // 默认1000km范围内
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            $userId = $this->userId;

            // 更新用户最新地理位置
            if ($userId) {
                Db::name("user")->where('id', $userId)->update(['longitude' => $param['longitude'], 'latitude' => $param['latitude']]);
            }

            // 虚拟位置用户数据
            $virtualUsers = [];
            /**if ($iPage == 1 && $userId) {
                if (class_exists('\api\app\module\virtual\VirtualUserModule')) {
                    $virtualUsers = \api\app\module\virtual\VirtualUserModule::getVirtualPosUser($userId, $this->yLevel, mt_rand(14,18), false);
                    if (! $virtualUsers) {
                        $virtualUsers = [];
                    }
                }
            }*/

            // 性别筛选 取与登录用户性别相反的数据
            if ($this->userSex == 1) {
                $whereSex = 'u.sex = 2';
            } elseif ($this->userSex == 2) {
                $whereSex = 'u.sex = 1';
            } else {
                $whereSex = '';
            }

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            // 获取客服uid
            $customUids = Db::name('role_user')->where('role_id', 3)->column('user_id');

            // 获取列表数据
            $userResult = Db::name('user')
                ->alias('u')
                ->join('user_setting s', 's.user_id = u.id', 'LEFT')
                ->join('user_token t', 't.user_id = u.id', 'LEFT')
                ->where('u.id <> ' . $userId)
                ->where('u.user_type > 1')
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2)
                ->where('u.virtual_pos = 0')
                ->where('u.y_level <= '.$this->yLevel)
                ->whereNotIn('u.id', $aBlockedUid)
//                ->where($whereSex)
                ->whereNotIn('u.id', $customUids) // 过滤掉客服
                ->field("u.*,s.video_cost,s.speech_cost,t.last_online_time,
                    (st_distance(point(u.longitude,u.latitude),point({$param['longitude']},{$param['latitude']}))*111195) as distance") // 要求mysql版本5.6以上
                ->having("distance <= {$param['distance']} ")
                ->order('distance', 'asc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $userResult) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($userResult as $row) {
                // 在线状态，机器人默认都在线
                if ($row['user_type'] == 3) {
                    $onlineState = 1;
                } else {
                    $onlineState = (time() - $row['last_online_time']) <= 1800 ? 1 : 0;
                }

                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'signature' => $row['signature'],
                    'sex' => $row['sex'],
                    'age' => $row['age'],
                    'province_name' => $row['province_name'],
                    'city_name' => $row['city_name'],
                    'district_name' => $row['district_name'],
                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                    'speech_cost' => isset($row['speech_cost']) ? $row['speech_cost'] : 0,
                    'video_cost' => isset($row['video_cost']) ? $row['video_cost'] : 0,
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'distance' => CommonModule::convertDistance($row['distance']),
                    'online_state' => $onlineState
                ];
            }

            // 获取banner
            $bannerResult = Db::name('banner')->where(['type' => 1, 'status' => 1])->order('sort')->select();
            $bannerList = [];
            if (! empty($bannerResult)) {
                foreach ($bannerResult as $banner) {
                    $bannerList[] = [
                        'img' => MaterialModule::getFullUrl($banner['img_url']),
                        'link' => $banner['a_url']
                    ];
                }
            }

            $this->success("OK", [
                'list' => array_merge($virtualUsers, $aRet),
                'banner' => $bannerList,
                'show_style' => 3
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 首页关注-我关注的用户
     */
    public function followUser()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            $result = Db::name('user_follow')
                ->alias('f')
                ->join('user u', 'u.id = f.be_user_id')
                ->join('user_setting s', 's.user_id = u.id', 'LEFT')
                ->join('user_token t', 't.user_id = u.id', 'LEFT')
                ->where("f.user_id={$userId}")
                ->where("f.status=1")
                ->where('u.user_type > 1')
                ->where('u.y_level <= '.$this->yLevel)
                ->whereNotIn('u.id', $aBlockedUid)
                ->where('u.id <> ' . $userId)
                ->field('u.*,(u.withdraw_coin+u.withdraw_frozen_coin+u.withdraw_used_coin) as total_coin,s.video_cost,s.speech_cost,t.online_status')
                ->order('total_coin', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result as $row) {
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'signature' => $row['signature'],
                    'sex' => $row['sex'],
                    'age' => $row['age'],
                    'province_name' => $row['province_name'],
                    'city_name' => $row['city_name'],
                    'district_name' => $row['district_name'],
                    'show_photo' => FandefModule::getAvatarFullUrl($row['avatar'], true),
                    'speech_cost' => isset($row['speech_cost']) ? $row['speech_cost'] : 0,
                    'video_cost' => isset($row['video_cost']) ? $row['video_cost'] : 0,
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'distance' => '0',
                    'online_state' => $row['online_status']
                ];
            }

            $this->success("OK", [
                'list' => $aRet,
                'banner' => [],
                'show_style' => 1
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 首页搜索，根据关注度倒序排列
     */
    public function searchUser()
    {
        try {
            $validate = new Validate([
                'keyword' => 'require|max:25',
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'keyword.require' => '请输入搜索词!',
                'keyword.max' => '搜索词长度不能超过25个字符!',
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $keyword = $param['keyword'];
            $iPage = $param['page'];
            $iPageSize = 15;
            $userId = $this->userId;

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            // 获取客服uid
            $customUids = Db::name('role_user')->where('role_id', 3)->column('user_id');
            array_push($customUids, $userId);

            // 计算总页数
            $total = Db::name('user')
                ->alias('u')
                ->whereNotIn('u.id', $customUids)
                ->whereNotIn('u.id', $aBlockedUid)
                ->where('u.id = "'.$keyword.'" OR u.user_nickname LIKE "%'.$keyword.'%"')
                ->where('u.user_type', 2)
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2)
                ->count();
            if ($total) {
                $totalPage = ceil($total / $iPageSize);
            } else {
                $totalPage = 0;
            }

            $result = Db::name('user')
                ->alias('u')
                ->whereNotIn('u.id', $customUids)
                ->where('u.id = "'.$keyword.'" OR u.user_nickname LIKE "%'.$keyword.'%"')
                ->where('u.user_type', 2)
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2)
                ->field('u.*')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->success("OK", []);
            }

            $aRet = [];
            foreach ($result as $row) {
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'show_photo' => FandefModule::getAvatarFullUrl($row['avatar'], true),
                    'signature' => $row['signature'],
                    'sex' => $row['sex'],
                    'age' => $row['age'],
                    'province_name' => $row['province_name'],
                    'city_name' => $row['city_name'],
                    'district_name' => $row['district_name'],
                ];
            }

            $this->success("OK", [
                'list' => $aRet,
                'total_page' => $totalPage
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 收入排行榜
     */
    public function incomeRanking()
    {
        try {
            $validate = new Validate([
                'type' => 'require|in:1,2,3,4', // 1:周榜 2:月榜 3:总榜 4:日榜
                'page' => 'integer|min:1'
            ]);

            $validate->message([
                'type.require' => '请输入类型!',
                'type.in' => '类型错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $page = !empty($param['page']) ? $param['page'] : 1;
            $iPageSize = 10; // 显示的条数
            $userId = $this->userId;

            if ($param['type'] == 1) {
                $startTime = strtotime(date('Y-m-d', time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)); // 本周一
                $endTime = time();
            } elseif ($param['type'] == 2) {
                $startTime = strtotime(date('Y-m-01 00:00:00'));
                $endTime = time();
            } elseif ($param['type'] == 3) {
                $startTime = 0;
                $endTime = time();
            } elseif ($param['type'] == 4) {
                $startTime = strtotime(date('Y-m-d 00:00:00'));
                $endTime = time();
            } else {
                $this->error('参数有误');
            }

            // 构建子查询sql
            $subQuery = Db::name('user_coin_record')
                ->alias('r')
                ->join('user u', 'u.id = r.user_id')
                ->where('r.class_id', 4)
                ->where("r.create_time >= {$startTime} AND r.create_time <= {$endTime}")
                ->group('r.user_id')
                ->field('SUM(r.change_coin) sum_coin,r.user_id,u.user_nickname,u.avatar,u.sex,u.city_name,u.vip_expire_time')
                ->buildSql();

            // 获取排名靠前的数据
            $result = Db::table($subQuery . ' as lis')
                ->join('user_follow f', 'f.be_user_id = lis.user_id AND f.user_id = ' . $userId, 'LEFT')
                ->order('lis.sum_coin', 'desc')
                ->order('lis.user_id', 'asc')
                ->field('lis.*,f.status as follow_status')
                ->page($page, $iPageSize)
                ->select();

            $aRet = [];
            foreach ($result as $id => $row) {
                $aRet[] = [
                    'num' => $id+1 + ($page-1)*$iPageSize,
                    'user_id' => $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'sex' => $row['sex'],
                    'city_name' => $row['city_name'],
                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'coin' => intval($row['sum_coin']),
                    'is_follow' => $row['follow_status'] ? 1 : 0 // 是否关注 1:关注 0:未关注
                ];
            }

            $this->success("OK", ['list' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 消费排行榜
     */
    public function payoutRanking()
    {
        try {
            $validate = new Validate([
                'type' => 'require|in:1,2,3,4', // 1:周榜 2:月榜 3:总榜 4:日榜
                'page' => 'integer|min:1'
            ]);

            $validate->message([
                'type.require' => '请输入类型!',
                'type.in' => '类型错误!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $page = !empty($param['page']) ? $param['page'] : 1;
            $iPageSize = 10; // 显示的条数
            $userId = $this->userId;

            if ($param['type'] == 1) {
                $startTime = strtotime(date('Y-m-d', time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)); // 本周一
                $endTime = time();
            } elseif ($param['type'] == 2) {
                $startTime = strtotime(date('Y-m-01 00:00:00'));
                $endTime = time();
            } elseif ($param['type'] == 3) {
                $startTime = 0;
                $endTime = time();
            } elseif ($param['type'] == 4) {
                $startTime = strtotime(date('Y-m-d 00:00:00'));
                $endTime = time();
            } else {
                $this->error('参数有误');
            }

            // 构建子查询sql
            $subQuery = Db::name('user_coin_record')
                ->alias('r')
                ->join('user u', 'u.id = r.user_id')
                ->where('r.class_id', 3)
                ->where("r.create_time >= {$startTime} AND r.create_time <= {$endTime}")
                ->group('r.user_id')
                ->field('SUM(r.change_coin) sum_coin,r.user_id,u.user_nickname,u.avatar,u.sex,u.city_name,u.vip_expire_time')
                ->buildSql();

            // 获取排名靠前的数据
            $result = Db::table($subQuery . ' as lis')
                ->join('user_follow f', 'f.be_user_id = lis.user_id AND f.user_id = ' . $userId, 'LEFT')
                ->order('lis.sum_coin', 'desc')
                ->order('lis.user_id', 'asc')
                ->field('lis.*,f.status as follow_status')
                ->page($page, $iPageSize)
                ->select();

            $aRet = [];
            foreach ($result as $id => $row) {
                $aRet[] = [
                    'num' => $id+1 + ($page-1)*$iPageSize,
                    'user_id' => $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'sex' => $row['sex'],
                    'city_name' => $row['city_name'],
                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'coin' => intval($row['sum_coin']),
                    'is_follow' => $row['follow_status'] ? 1 : 0 // 是否关注 1:关注 0:未关注
                ];
            }

            $this->success("OK", ['list' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
