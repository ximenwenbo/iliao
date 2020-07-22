<?php
/**
 * User: coase
 * Date: 2019-06-03
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use dctxyun\Liveapi;
use api\app\module\VipModule;
use api\app\module\WatchModule;
use api\app\module\LiveModule;
use api\app\module\UserModule;
use api\app\module\MaterialModule;
use api\app\module\ConfigModule;

/**
 * #####直播的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.获取直播频道列表
 * 2.获取直播中房间列表
 * 3.
 * ``````````````````
 */
class LiveController extends RestBaseController
{
    /**
     * 获取直播频道列表
     */
    public function getChannelList()
    {
        try {
            $channelResult = Db::name('live_channel')->where('status', 1)->field('id,name,description,icon')->select();

            $this->success("OK", ['channel_list' => $channelResult]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取直播间列表
     */
    public function getLivingList()
    {
        try {
            $validate = new Validate([
                'page' => 'require|min:1',
                'channel_id' => 'integer',
                'scene_id' => 'integer'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!',
                'channel_id.integer' => '频道id错误!',
                'scene_id.integer' => 'scene id错误!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            $iPage = $param['page'];
            $iPageSize = 10;

            $query = Db::name('user')
                ->alias('u')
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2);

            if (! empty($param['scene_id']) && $param['scene_id'] == 2) {
                $query->join('live_home l', 'l.user_id=u.id AND l.status=1');
            } else {
                $query->join('live_home l', 'l.user_id=u.id AND l.status=1');
            }

            if (! empty($param['channel_id'])) {
                $query->where('l.channel_id', $param['channel_id']);
            }

            $liveResult = $query->field('l.*,u.id uid,u.user_nickname,u.avatar,u.sex,u.withdraw_coin,u.withdraw_used_coin')
                ->order('l.online_viewer', 'desc')
                ->order('l.total_viewer', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            $liveList = [];
            foreach ($liveResult as $row) {
                $liveList[] = [
                    'live_id' => $row['id'] ? : 0,
                    'user_id' => $row['uid'],
                    'user_nickname' => $row['user_nickname'],
                    'avatar' => $row['avatar'],
                    'sex' => $row['sex'],
                    'title' => $row['title'] ? : '',
                    'cover_img' => !empty($row['cover_img']) ? $row['cover_img'] : $row['avatar'],
                    'live_mode' => $row['live_mode'] ? : 0,
                    'type' => $row['type'] ? : 0,
                    'in_coin' => $row['in_coin'] ? : 0,
                    'start_time' => $row['start_time'] ? : 0,
                    'city_name' => $row['city_name'] ? : '',
                    'viewer' => $row['online_viewer'] ? : 0,
                    'total_coin_num' => $row['withdraw_coin'] + $row['withdraw_used_coin'], // 总的可提现金币数
                    'watch_num' => Db::name('watch_relation')->where("live_user_id={$row['uid']} AND watch_expire_time>=".strtotime(date('Y-m-d')))->count(), // 守护数
                ];
            }

            $this->success("OK", [
                'live_list' => $liveList,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取直播中的
     */
    public function getLivingUser4Lianmai()
    {
        try {
            $validate = new Validate([
                'page' => 'require|min:1',
                'channel_id' => 'integer',
                'scene_id' => 'integer'
            ]);

            $validate->message([
                'page.require' => '请输入当前页!',
                'channel_id.integer' => '频道id错误!',
                'scene_id.integer' => 'scene id错误!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            $iPage = $param['page'];
            $iPageSize = 10;

            $query = Db::name('user')
                ->alias('u')
                ->join('live_home l', 'l.user_id=u.id AND l.status=1', 'left')
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2)
                ->order('l.id', 'desc')
                ->order('u.id', 'desc');

            if (! empty($param['channel_id'])) {
                $query->where('l.channel_id', $param['channel_id']);
            }

            $liveResult = $query->field('l.*,u.id uid,u.user_nickname,u.avatar,u.sex,u.withdraw_coin,u.withdraw_used_coin')
                ->page($iPage, $iPageSize)
                ->select();

            $liveList = [];
            foreach ($liveResult as $row) {
                $liveList[] = [
                    'live_id' => $row['id'] ? : 0,
                    'user_id' => $row['uid'],
                    'user_nickname' => $row['user_nickname'],
                    'avatar' => $row['avatar'],
                    'sex' => $row['sex'],
                    'title' => $row['title'] ? : '',
                    'cover_img' => !empty($row['cover_img']) ? $row['cover_img'] : $row['avatar'],
                    'live_mode' => $row['live_mode'] ? : 0,
                    'type' => $row['type'] ? : 0,
                    'in_coin' => $row['in_coin'] ? : 0,
                    'start_time' => $row['start_time'] ? : 0,
                    'city_name' => $row['city_name'] ? : '',
                    'viewer' => $row['online_viewer'] ? : 0,
                    'total_coin_num' => $row['withdraw_coin'] + $row['withdraw_used_coin'], // 总的可提现金币数
                    'watch_num' => Db::name('watch_relation')->where("live_user_id={$row['uid']} AND watch_expire_time>=".strtotime(date('Y-m-d')))->count(), // 守护数
                ];
            }

            $this->success("OK", [
                'live_list' => $liveList,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 发起直播
     */
    public function launchLive()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'title' => 'require|min:1|max:100', // 标题
                'channel_id' => 'require|integer', // 频道id
                'live_mode' => 'integer|in:1,2', // 直播类型(1:视频直播 2:语音直播)
                'type' => 'require|integer|in:1,2,3', // 房间类型(1:普通房间 2:密码房间 3:门票房间)
                'in_password' => 'requireIf:type,2|min:3|max:10', // 房间密码, type=2时必填
                'in_coin' => 'requireIf:type,3|integer|min:1', // 门票金币数, type=3时必填
            ]);

            $validate->message([
                'title.require' => '请输入房间名称!',
                'channel_id.require' => '请输入频道!',
                'type.require' => '请输入房间类型!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($param['type'] == 3 && $param['in_coin'] < 1) {
                $this->error('收费必须大于0');
            }

            $param['live_mode'] = isset($param['live_mode']) ? $param['live_mode'] : 1;

            $userFind = Db::name('user')->find($userId);

            if ($userFind['daren_status'] != 2) {
                $this->error('您还不是主播，请先认证主播');
            }

            // 如果有存在未关闭的直播间，则先关闭直播间
            Db::name('live_home')->where(['user_id'=>$userId, 'status'=>1])->update([
                'status' => 3,
                'error_msg' => '直播间未正常关闭，由发起直播接口强制修改状态关闭'
            ]);

            // 新增直播间信息
            $liveId = Db::name('live_home')->insertGetId([
                'user_id' => $userId,
                'title' => trim($param['title']),
                'channel_id' => $param['channel_id'],
                'live_mode' => $param['live_mode'],
                'type' => $param['type'],
                'cover_img' => !empty($param['cover_img']) ? $param['cover_img'] : '',
                'city_name' => !empty($param['city_name']) ? $param['city_name'] : '',
                'in_password' => !empty($param['in_password']) ? md5($param['in_password']) : '',
                'in_coin' => !empty($param['in_coin']) ? $param['in_coin'] : 0,
            ]);

            // 生成推流地址
            $streamId = 'live_' . $userId;
            $tLiveUrl = LiveModule::createPushStream($userId, $streamId, ['option_type'=>2, 'option_id'=>$liveId, 'option_class_id'=>21]);

            // 开播任务 start
            /**
            $jobHandlerClassName = 'app\admin\job\RobotInoutLive'; // 当前任务将由哪个类来负责处理。
            $jobQueueName = 'bullet_join_robot_viewer'; // 当前任务归属的队列名称,如果为新队列，会自动创建(bullet_screen_gift_msg:礼物消息弹幕)
            $jobData = [ // 当前任务所需的业务数据,不能为 resource 类型，其他类型最终将转化为json形式的字符串(jobData 为对象时，存储其public属性的键值对)
                'user_id' => $userId, // 主播uid
                'live_id' => $liveId, // 直播间id
                'create_time' => time(),
                'bizId' => uniqid()
            ];
            $isPushed = \think\Queue::push($jobHandlerClassName , $jobData , $jobQueueName); // 将该任务推送到消息队列，等待对应的消费者去执行
            if ($isPushed === false) {
                Log::write(sprintf('%s：直播间开播插入队列错误，返回:%s', __METHOD__, var_export($isPushed, true)),'error');
            }
             */
            // 开播任务 end

            $this->success("OK", [
                'viewer_num' => 0,
                't_live_url' => $tLiveUrl, // 推流url
                'live_user_info' => [
                    'user_id' => $userFind['id'],
                    'user_nickname' => $userFind['user_nickname'],
                    'avatar' => $userFind['avatar'],
                    'sex' => $userFind['sex'],
                    'total_coin_num' => $userFind['withdraw_coin'] + $userFind['withdraw_used_coin'], // 总的可提现金币数
                    'watch_num' => Db::name('watch_relation')->where("live_user_id={$userId} AND watch_expire_time>=".strtotime(date('Y-m-d')))->count(), // 守护数
                ],
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 加入直播间
     */
    public function joinLive()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
                'in_password' => 'min:1|max:10', // 房间密码
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            if (Db::name('live_remove_setting')->where(['user_id'=>$userId,'live_uid'=>$param['user_id']])->count()) {
                $this->error('您被踢出该直播间，不能观看直播');
            }

            $publicConfig = cmf_get_option('public_config');
            $vipLimit = $publicConfig['public_config']['VipLimit'];
            if (!empty($vipLimit['status']) && $vipLimit['status']==1 && !empty($vipLimit['startDate']) && !empty($vipLimit['endDate'])) {
                $time = time();
                if ($time < strtotime($vipLimit['startDate']) || $time > strtotime($vipLimit['endDate'])) {
                    $vipExpireTime = Db::name('user')->where('id', $userId)->value('vip_expire_time');
                    if (! VipModule::checkIsVip($vipExpireTime)) {
                        $this->error('必须是VIP才能进入');
                    }
                }
            }

            if ($liveFind['type'] == 2) {
                // 密码直播
                if (md5($param['in_password']) != $liveFind['in_password']) {
                    $this->error('房间密码错误');
                }
            } elseif ($liveFind['type'] == 3) {
                // 门票直播，扣费
                if (LiveModule::charge4costLive($userId, $liveFind['id']) === false) {
                    // 扣费失败
                    $this->error(LiveModule::$errMessage);
                }
            }

            // 获取观众用户数据
            $viewerUserFind = Db::name('user')->find($userId);

            // 获取主播用户数据
            $liveUserFind = Db::name('user')->find($liveFind['user_id']);

            // 生成直播播放流地址
            $streamId= Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $liveFind['id'], 'option_class_id' => 21])
                ->value('stream_id');
            $bLiveUrl = \dctxyun\Common::getBLiveUrl($streamId);

            // 新增/更新直播间观看记录
            $existViewerFind = Db::name('live_home_viewer')->where(['user_id'=>$userId,'live_id'=>$liveFind['id']])->find();
            if ($existViewerFind) {
                if ($existViewerFind['status'] != 1) {
                    // 更新观看记录
                    Db::name('live_home_viewer')->where(['user_id' => $userId, 'live_id' => $liveFind['id']])->update(['status' => 1]);

                    // 在线观看者+1
                    Db::name('live_home')->where('id', $liveFind['id'])->setInc('online_viewer', 1);
                }
            } else {
                // 新增观看记录
                Db::name('live_home_viewer')->insert([
                    'user_id' => $userId,
                    'live_id' => $liveFind['id'],
                    'live_user_id' => $param['user_id'],
                ]);

                // 总观看者+1 & 在线观看者+1
                Db::name('live_home')->where('id', $liveFind['id'])->update([
                    'online_viewer' => Db::raw('online_viewer+1'),
                    'total_viewer' => Db::raw('total_viewer+1'),
                ]);
            }

            # 是否关注
            if (Db::name('user_follow')->where(['user_id'=>$userId, 'be_user_id'=>$param['user_id'], 'status'=>1])->count()) {
                $isFollow = 1; // 关注
            } else {
                $isFollow = 0; // 未关注
            }

            $this->success("OK", [
                'b_live_url' => $bLiveUrl, // 直播流地址
                'viewer_num' => Db::name('live_home_viewer')->where(['live_id'=>$liveFind['id'], 'status'=>1])->count(),
                'is_follow' => $isFollow,
                'viewer_user' => [ // 加入者（观众）信息
                    'user_level' => UserModule::getUserLevelByUid($userId), // 用户财富等级
                    'is_vip' => VipModule::checkIsVip($viewerUserFind['vip_expire_time']), // 是否vip (1:是 0:否)
                    'is_watch' => WatchModule::checkIsWatch($userId, $liveFind['user_id']), // 是否守护 (1:是 0:否)
                    'is_manager' => LiveModule::checkIsManager($userId, $param['user_id']), // 是否管理员（1:是 0:否）
                    'is_banspeech' => LiveModule::checkIsBanspeech($userId, $param['user_id'], $liveFind['id']), // 是否禁言（1:是 0:否）
                ],
                'live_user' => [ // 主播信息
                    'user_id' => $liveUserFind['id'],
                    'user_nickname' => $liveUserFind['user_nickname'],
                    'avatar' => $liveUserFind['avatar'],
                    'sex' => $liveUserFind['sex'],
                    'total_coin_num' => $liveUserFind['withdraw_coin'] + $liveUserFind['withdraw_used_coin'], // 总的可提现金币数
                    'watch_num' => Db::name('watch_relation')->where("live_user_id={$liveFind['user_id']} AND watch_expire_time>=".strtotime(date('Y-m-d')))->count(), // 守护数
                ],
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 离开直播间
     */
    public function leaveLive()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where('user_id', $param['user_id'])->where('status > 0')->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            if ($userId == $param['user_id']) {
                // 主播关闭房间
                if (LiveModule::closeLiveHome($liveFind)) {
                    $this->success("OK");
                } else {
                    $this->error('关闭直播间异常');
                }
            }

            $existViewerFind = Db::name('live_home_viewer')->where(['user_id' => $userId, 'live_id' => $liveFind['id']])->find();
            if (! $existViewerFind) {
                $this->error("该用户不在聊天室");
            }

            if ($existViewerFind['status'] == 1) {
                // 更新直播间观看记录
                Db::name('live_home_viewer')->where(['user_id' => $userId, 'live_id' => $liveFind['id']])->update(['status' => 0]);

                // 在线观看者-1
                Db::name('live_home')->where('id', $liveFind['id'])->setDec('online_viewer', 1);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取直播间观众
     */
    public function getLiveViewer()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            $viewerList = Db::name('live_home_viewer')
                ->alias('v')
                ->join('user u', 'u.id = v.user_id')
                ->where(['live_id' => $liveFind['id'], 'status' => 1])
                ->field('u.id user_id,u.user_nickname,u.avatar')
                ->limit(100)
                ->select();

            $this->success("OK", [
                'list' => $viewerList,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 申请连麦（观众发起）
     */
    public function applyLianmai()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($userId == $param['user_id']) {
                $this->error('不能和自己连麦');
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            // 生成推流地址
            $streamId = 'live_' . $userId;
            $tLiveUrl = LiveModule::createPushStream($userId, $streamId, ['option_type'=>2, 'option_id'=>$liveFind['id'], 'option_class_id'=>22]);

            $this->success("OK", [
                't_live_url' => $tLiveUrl, // 推流地址
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 申请连麦（主播发起）
     */
    public function applyLianmai4Live()
    {
        $launchUid = $this->getUserId();

        try {
            $validate = new Validate([
                'accept_uid' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'accept_uid.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $acceptUserFind = Db::name('user')->find($param['accept_uid']);

            $launchLiveFind = Db::name('live_home')->where(['user_id' => $launchUid, 'status' => 1])->find();
            if (! $launchLiveFind) {
                $this->error('当前主播还没有开播');
            }
            $acceptLiveFind = Db::name('live_home')->where(['user_id' => $param['accept_uid'], 'status' => 1])->find();
            if (! $acceptLiveFind) {
                $this->error('邀请主播还没有开播');
            }

            $launchStreamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $launchLiveFind['id'], 'status' => 1])
                ->find();

            $acceptStreamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $acceptLiveFind['id'], 'status' => 1])
                ->find();

            // 生成两个混流
            // 发起者混流
            Liveapi::startMixStream4PK($launchStreamFind['stream_id'], $acceptStreamFind['stream_id'], $launchStreamFind['mix_stream_session_id']);
            //接受者混流
            Liveapi::startMixStream4PK($acceptStreamFind['stream_id'], $launchStreamFind['stream_id'], $acceptStreamFind['mix_stream_session_id']);

            // 连麦记录数据
            Db::name('live_lianmai_record')->insert([
                'launch_uid' => $launchUid,
                'accept_uid' => $param['accept_uid'],
                'start_time' => time(),
                'status' => 1
            ]);

            $this->success("OK", [
                'accept_user_info' => [ // 接受者用户信息
                    'user_id' => $acceptUserFind['id'],
                    'sex' => $acceptUserFind['sex'],
                    'user_nickname' => $acceptUserFind['user_nickname'],
                    'avatar' => $acceptUserFind['avatar'],
                ],
                'home_cover_img' => $acceptLiveFind['cover_img'],
                'accept_b_live_quick_url' => \dctxyun\Common::getBLiveUrl($acceptStreamFind['stream_id'], [], true)
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 关闭连麦（主播发起）
     */
    public function closeLianmai4Live()
    {
        $launchUid = $this->getUserId();

        try {
            $validate = new Validate([
                'launch_uid' => 'require|integer', // 发起者uid
                'accept_uid' => 'require|integer', // 接受者uid
            ]);

            $validate->message([
                'launch_uid.require' => '请输入发起者id!',
                'accept_uid.require' => '请输入接受者id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $launchLiveFind = Db::name('live_home')->where(['user_id' => $launchUid, 'status' => 1])->find();
            if (! $launchLiveFind) {
                $this->error('当前主播还没有开播');
            }
            $acceptLiveFind = Db::name('live_home')->where(['user_id' => $param['accept_uid'], 'status' => 1])->find();
            if (! $acceptLiveFind) {
                $this->error('邀请主播还没有开播');
            }

            $launchStreamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $launchLiveFind['id'], 'status' => 1])
                ->find();

            $acceptStreamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $acceptLiveFind['id'], 'status' => 1])
                ->find();

            // 发起者取消混流
            Liveapi::cancelMixStream($launchStreamFind['stream_id'], $launchStreamFind['mix_stream_session_id']);
            //接受者取消混流
            Liveapi::cancelMixStream($acceptStreamFind['stream_id'], $acceptStreamFind['mix_stream_session_id']);

            // 结束连麦记录数据
            Db::name('live_lianmai_record')
                ->where(['launch_uid' => $param['launch_uid'], 'accept_uid' => $param['accept_uid'], 'status' => 1])
                ->update(['end_time' => time(), 'status' => 2]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取直播房间中的所有流
     */
    public function getTStreamsByLive()
    {
        $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->find();
            if (! $liveFind) {
                $this->success("OK", []);
            }

            $streamSelect = Db::name('live_stream')->alias('s')
                ->join('user u', 'u.id = s.user_id')
                ->where(['s.option_type'=>2, 's.option_id'=>$liveFind['id'], 's.status'=>1])
                ->field('s.*,u.sex,u.avatar,u.user_nickname')
                ->select();

            foreach ($streamSelect as $item) {
                $streamList[] = [
                    'user_info' => [
                        'user_id' => $item['user_id'],
                        'sex' => $item['sex'],
                        'user_nickname' => $item['user_nickname'],
                        'avatar' => $item['avatar'],
                    ],
                    'b_live_url' => \dctxyun\Common::getBLiveUrl($item['stream_id']),
                    'b_live_quick_url' => \dctxyun\Common::getBLiveUrl($item['stream_id'], [], true)
                ];
            }

            $this->success("OK", [
                'stream_list' => !empty($streamList) ? $streamList : [], // 推流地址
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 开始pk
     */
    public function startPK()
    {
        $this->getUserId();

        try {
            $validate = new Validate([
                'launch_uid' => 'require|integer', // pk发起者id
                'accept_uid' => 'require|integer', // pk接受者id
            ]);

            $validate->message([
                'launch_uid.require' => '请输入发起者id!',
                'accept_uid.require' => '请输入接受者id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $launchLiveFind = Db::name('live_home')->where(['user_id' => $param['launch_uid'], 'status' => 1])->find();
            if (! $launchLiveFind) {
                $this->error('当前主播还没有开播');
            }
            $acceptLiveFind = Db::name('live_home')->where(['user_id' => $param['accept_uid'], 'status' => 1])->find();
            if (! $acceptLiveFind) {
                $this->error('邀请主播还没有开播');
            }

            // pk记录数据
            Db::name('live_pk_record')->insert([
                'launch_uid' => $param['launch_uid'],
                'accept_uid' => $param['accept_uid'],
                'start_time' => time(), // 延时3秒
                'status' => 1
            ]);

            $this->success("OK", [
//                'rest_time' => 7*60 + 3 // PK时间5分钟 + 惩罚时间2分钟 + 延时3秒
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 结束pk
     */
    public function endPK()
    {
        $this->getUserId();

        try {
            $validate = new Validate([
                'launch_uid' => 'require|integer', // pk发起者id
                'accept_uid' => 'require|integer', // pk接受者id
                'launch_get_coin' => 'require|integer', // pk发起者获得金币数
                'accept_get_coin' => 'require|integer', // pk接受者获得金币数
                'pk_result' => 'require|in:0,1,2', // pk结果（0平局，1发起方胜，2接受方胜）
            ]);

            $validate->message([
                'launch_uid.require' => '请输入发起者id!',
                'accept_uid.require' => '请输入接受者id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $launchLiveFind = Db::name('live_home')->where(['user_id' => $param['launch_uid'], 'status' => 1])->find();
            if (! $launchLiveFind) {
                $this->error('当前主播还没有开播');
            }
            $acceptLiveFind = Db::name('live_home')->where(['user_id' => $param['accept_uid'], 'status' => 1])->find();
            if (! $acceptLiveFind) {
                $this->error('邀请主播还没有开播');
            }

            $latestAutoId = Db::name('live_pk_record')
                ->where(['launch_uid' => $param['launch_uid'], 'accept_uid' => $param['accept_uid'], 'status' => 1])
                ->order('id', 'desc')
                ->value('id');
            if (! $latestAutoId) {
                $this->error('无PK记录');
            }

            // pk记录数据
            Db::name('live_pk_record')
                ->where(['id' => $latestAutoId, 'status' => 1])
                ->update([
                    'launch_get_coin' => $param['launch_get_coin'],
                    'accept_get_coin' => $param['accept_get_coin'],
                    'pk_result' => $param['pk_result'],
                    'end_time' => time(),
                    'status' => 2
                ]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取本场pk剩余时间
     */
    public function getRestTime4PK()
    {return false; // todo 暂时不用 2019-06-24
        $this->getUserId();

        try {
            $validate = new Validate([
                'launch_uid' => 'require|integer', // pk发起者id
                'accept_uid' => 'require|integer', // pk接受者id
            ]);

            $validate->message([
                'launch_uid.require' => '请输入发起者id!',
                'accept_uid.require' => '请输入接受者id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }


            $pkFind = Db::name('live_pk_record')->where(['launch_uid' => $param['launch_uid'], 'accept_uid' => $param['accept_uid'], 'status' => 1])->find();
            if (! $pkFind) {
                $this->error('当前没有pk');
            }

            $this->success("OK", [
                'rest_time' => $pkFind['start_time'] - time() + 5*60 + 2*60
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取主播的播放流
     */
    public function getUserStreams()
    {
        $this->getUserId();

        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'user_id.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userFind = Db::name('user')->find($param['user_id']);

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->find();
            if (! $liveFind) {
                $this->success("OK", []);
            }

            $streamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $liveFind['id'], 'user_id' => $param['user_id'], 'status' => 1])
                ->find();

            $this->success("OK", [
                'user_info' => [ // 主播个人信息
                    'user_id' => $userFind['id'],
                    'sex' => $userFind['sex'],
                    'user_nickname' => $userFind['user_nickname'],
                    'avatar' => $userFind['avatar'],
                ],
                'home_cover_img' => $liveFind['cover_img'],
                'b_live_url' => \dctxyun\Common::getBLiveUrl($streamFind['stream_id']), // 播放流
                'b_live_quick_url' => \dctxyun\Common::getBLiveUrl($streamFind['stream_id'], [], true), // 加速播放流
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取某个用户信息 - 直播中浏览用户展示的数据
     */
    public function getUserInfo4Live()
    {
        try {
            $currUserId = $this->userId;

            $validate = new Validate([
                'user_id' => 'require|integer',
                'live_uid' => 'require|integer',
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
                'live_uid.require' => '请输入主播id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userInfo = Db::name("user")->find($param['user_id']);
            if (! $userInfo) {
                $this->error('用户不存在!');
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['live_uid'], 'status' => 1])->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            # 是否关注
            if ($currUserId && Db::name('user_follow')->where(['user_id'=>$currUserId, 'be_user_id'=>$param['user_id'], 'status'=>1])->count()) {
                $isFollow = 1; // 关注
            } else {
                $isFollow = 0; // 未关注
            }

            # 拼装返回值
            $userRet = [
                'user_id' => $userInfo['id'],
                'user_nickname' => $userInfo['user_nickname'],
                'sex' => $userInfo['sex'],
                'age' => $userInfo['age'],
                'used_coin' => $userInfo['used_coin'],
                'avatar' => MaterialModule::getFullUrl($userInfo['avatar']), // 头像
                'signature' => htmlspecialchars_decode($userInfo['signature']),
                'is_vip' => VipModule::checkIsVip($userInfo['vip_expire_time']), // 是否vip (1:是 0:否)
                'be_follow_num' => $userInfo['be_follow_num'], // 被关注的人数
                'follow_num' => Db::name("user_follow")->where(['user_id' => $param['user_id'], 'status' => 1])->count(), // 关注的人数
                'is_manager' => LiveModule::checkIsManager($param['user_id'], $param['live_uid']), // 是否管理员（1:是 0:否）
                'is_banspeech' => LiveModule::checkIsBanspeech($param['user_id'], $param['live_uid'], $liveFind['id']), // 是否禁言（1:是 0:否）
            ];

            $this->success("OK", [
                'user_info' => $userRet,
                'is_follow' => $isFollow,
                'is_manager' => LiveModule::checkIsManager($currUserId, $param['live_uid']), // 是否管理员（1:是 0:否）
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间管理员列表
     */
    public function getManagerList()
    {
        $userId = $this->getUserId();

        try {
//            $validate = new Validate([
//                'live_uid' => 'require|integer',
//            ]);
//
//            $validate->message([
//                'live_uid.require' => '请输入主播id!',
//            ]);
//
//            $param = $this->request->param();
//            if (!$validate->check($param)) {
//                $this->error($validate->getError());
//            }

            $managerSelect = Db::name('live_manager_setting')
                ->alias('m')
                ->join('user u', 'u.id = m.user_id')
                ->where('m.live_uid', $userId)
                ->field('u.id user_id,u.user_nickname,u.avatar,u.sex')
                ->select()
                ->toArray();

            $this->success("OK", [
                'list' => $managerSelect,
                'extra' => [
                    'max_manager' => 5, // 最多允许的管理员数
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间增加管理员
     */
    public function addManager()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'manager_uid' => 'require|integer',
            ]);

            $validate->message([
                'manager_uid.require' => '请输入管理员id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($userId == $param['manager_uid']) {
                $this->error('不能添加自己');
            }

            if (Db::name('live_manager_setting')->where('live_uid', $userId)->count() >= 5) {
                $this->error('最多只能添加5个管理员');
            }

            if (! Db::name('live_manager_setting')->where(['user_id'=>$param['manager_uid'], 'live_uid'=>$userId])->count()) {
                Db::name('live_manager_setting')->insert([
                    'user_id' => $param['manager_uid'],
                    'live_uid' => $userId
                ]);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间删除管理员
     */
    public function delManager()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'manager_uid' => 'require|integer',
            ]);

            $validate->message([
                'manager_uid.require' => '请输入管理员id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($userId == $param['manager_uid']) {
                $this->error('不能删除自己');
            }

            if (Db::name('live_manager_setting')->where(['user_id'=>$param['manager_uid'], 'live_uid'=>$userId])->count()) {
                Db::name('live_manager_setting')->where(['user_id'=>$param['manager_uid'], 'live_uid'=>$userId])->delete();
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间禁言
     */
    public function banspeechUser()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'type' => 'require|integer|in:1,2', // 1:本场禁言 2:永久禁言
                'banspeech_uid' => 'require|integer', // 被禁言者id
                'live_uid' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'banspeech_uid.require' => '请输入被禁言者id!',
                'live_uid.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id'=>$param['live_uid'],'status'=>1])->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            Db::name('live_banspeech_setting')->where(['user_id'=>$param['banspeech_uid'],'live_uid'=>$param['live_uid']])->delete();

            // 主播禁言
            if ($userId == $param['live_uid']) {
                Db::name('live_banspeech_setting')->insert([
                    'type' => $param['type'],
                    'user_id' => $param['banspeech_uid'],
                    'live_uid' => $param['live_uid'],
                    'live_id' => $liveFind['id'],
                    'operate_uid' => $userId
                ]);

                $this->success("OK");
            }

            if (! Db::name('live_manager_setting')->where(['user_id'=>$userId,'live_uid'=>$param['live_uid']])->count()) {
                $this->error('您不是直播间的管理员');
            }

            // 管理员禁言
            Db::name('live_banspeech_setting')->insert([
                'type' => $param['type'],
                'user_id' => $param['banspeech_uid'],
                'live_uid' => $param['live_uid'],
                'live_id' => $liveFind['id'],
                'operate_uid' => $userId
            ]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间取消禁言
     */
    public function unbanspeechUser()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'banspeech_uid' => 'require|integer', // 被禁言者id
                'live_uid' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'banspeech_uid.require' => '请输入被禁言者id!',
                'live_uid.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id'=>$param['live_uid'],'status'=>1])->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            if (!Db::name('live_banspeech_setting')->where(['user_id'=>$param['banspeech_uid'],'live_uid'=>$param['live_uid']])->count()) {
                $this->error("没有被禁言");
            }

            // 主播取消禁言
            if ($userId == $param['live_uid']) {
                Db::name('live_banspeech_setting')->where(['user_id'=>$param['banspeech_uid'],'live_uid'=>$param['live_uid']])->delete();

                $this->success("OK");
            }

            if (!Db::name('live_manager_setting')->where(['user_id'=>$userId,'live_uid'=>$param['live_uid']])->count()) {
                $this->error('您不是直播间的管理员');
            }

            // 管理员取消禁言
            Db::name('live_banspeech_setting')->where(['user_id'=>$param['banspeech_uid'],'live_uid'=>$param['live_uid']])->delete();

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间踢人
     */
    public function removeUser()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'remove_uid' => 'require|integer', // 被踢者id
                'live_uid' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'remove_uid.require' => '请输入被踢者id!',
                'live_uid.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            if (Db::name('live_remove_setting')->where(['user_id'=>$param['remove_uid'],'live_uid'=>$param['live_uid']])->count()) {
                $this->error("已经被踢出过了");
            }

            // 主播踢人
            if ($userId == $param['live_uid']) {
                Db::name('live_remove_setting')->insert([
                    'user_id' => $param['remove_uid'],
                    'live_uid' => $param['live_uid'],
                    'operate_uid' => $userId
                ]);

                $this->success("OK");
            }

            if (! Db::name('live_manager_setting')->where(['user_id'=>$userId,'live_uid'=>$param['live_uid']])->count()) {
                $this->error('您不是直播间的管理员');
            }

            // 管理员踢人
            Db::name('live_remove_setting')->insert([
                'user_id' => $param['remove_uid'],
                'live_uid' => $param['live_uid'],
                'operate_uid' => $userId
            ]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 用户在直播间信息状态
     */
    public function getUserStatus4Live()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'live_uid' => 'require|integer', // 主播id
            ]);

            $validate->message([
                'live_uid.require' => '请输入主播id!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $liveFind = Db::name('live_home')->where(['user_id' => $param['live_uid'], 'status' => 1])->order('id', 'desc')->find();
            if (! $liveFind) {
                $this->error('主播还没有开播');
            }

            $userInfo = Db::name('user')->find($userId);

            $userLevel = UserModule::getUserLevelByUid($userId);

            $publicConfig = cmf_get_option('public_config');
            if (isset($publicConfig['public_config']['UserLevelLimit']['barrage']) &&
                $publicConfig['public_config']['UserLevelLimit']['barrage'] > $userLevel)
            {
                $isBulletscreen = 0;
            } else {
                $isBulletscreen = 1;
            }

            $this->success("OK", [
                'user_level' => $userLevel, // 用户财富等级
                'is_vip' => VipModule::checkIsVip($userInfo['vip_expire_time']), // 是否vip (1:是 0:否)
                'is_watch' => WatchModule::checkIsWatch($userId, $param['live_uid']), // 是否守护 (1:是 0:否)
                'is_manager' => LiveModule::checkIsManager($userId, $param['live_uid']), // 是否管理员（1:是 0:否）
                'is_banspeech' => LiveModule::checkIsBanspeech($userId, $param['live_uid'], $liveFind['id']), // 是否禁言（1:是 0:否）
                'is_bulletscreen' => $isBulletscreen, // 是否可以发弹幕（1:是 0:否）
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 直播间消费贡献排行榜
     */
    public function payoutRanking4Living()
    {
        try {
            $validate = new Validate([
                'live_uid' => 'require|integer', // 主播id
                'type' => 'require|in:1,2,3,4', // 1:周榜 2:月榜 3:总榜 4:日榜
                'page' => 'integer|min:1'
            ]);

            $validate->message([
                'live_uid.require' => '请输入主播id!',
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
            $subQuery = Db::name('gift_given_order')
                ->alias('o')
                ->join('user u', 'u.id = o.send_uid')
                ->where('o.receive_uid', $param['live_uid'])
                ->where("o.send_time >= {$startTime} AND o.send_time <= {$endTime}")
                ->group('o.send_uid')
                ->field('SUM(o.total_coin) sum_coin,u.id as user_id,u.user_nickname,u.avatar,u.sex,u.city_name,u.vip_expire_time')
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
