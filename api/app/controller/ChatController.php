<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 17:50
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\ChatModule;
use api\app\module\UserModule;
use api\app\module\MaterialModule;
use api\app\module\ConfigModule;

/**
 * #####聊天的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.发起聊天
 * 2.更新聊天时长，每分钟更新一次
 * 3.关闭聊天
 * ``````````````````
 */
class ChatController extends RestUserBaseController
{
    /**
     * 判断用户是否可以聊天
     */
    public function getChatState()
    {
        try {
            $validate = new Validate([
                'accept_uid' => 'integer', // 聊天对象uid
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            $userInfo = Db::name("user")->find($userId);
            if (!$userInfo) {
                $this->error('参数获取失败!');
            }

            if (empty($param['accept_uid'])) {
                $this->success("OK", [
                    'code' => 0,
                    'is_msg' => 0,
                    'is_msg_err' => '当前版本不支持，请下载最新包',
                    'is_voice' => 0,
                    'is_video' => 0,
                    'is_voice_callback' => 0,
                    'is_video_callback' => 0,
                ]);
            }
            $acceptUserInfo = Db::name('user')->find($param['accept_uid']);
            if (!$acceptUserInfo) {
                $this->error('参数获取失败!');
            }

            // 客服
            $customerOption = cmf_get_option('customer_config');
            $uids = $customerOption['customer_config']['customer']['uid'];
            $aKfUid = explode(',', $uids);
            if (in_array($userId, $aKfUid) || in_array($param['accept_uid'], $aKfUid)) {
                $this->success("OK", [
                    'code' => 0,
                    'is_msg' => 1,
                    'is_msg_err' => '',
                    'is_voice' => 0,
                    'is_video' => 0,
                    'is_voice_callback' => 0,
                    'is_video_callback' => 0,
                ]);
            }

            if ($userInfo['daren_status'] == 2 && $acceptUserInfo['daren_status'] == 2) {
                $this->success("OK", [
                    'code' => 0,
                    'is_msg' => 0,
                    'is_msg_err' => '主播与主播不能发消息',
                    'is_voice' => 0,
                    'is_video' => 0,
                    'is_voice_callback' => 0,
                    'is_video_callback' => 0,
                ]);
            }

            if ($userInfo['daren_status'] != 2 && $acceptUserInfo['daren_status'] != 2) {
                $this->success("OK", [
                    'code' => 0,
                    'is_msg' => 0,
                    'is_msg_err' => '用户与用户不能发消息',
                    'is_voice' => 0,
                    'is_video' => 0,
                    'is_voice_callback' => 0,
                    'is_video_callback' => 0,
                ]);
            }

            if ($userInfo['daren_status'] != 2) {
                // 触发者是用户
                $zhuboVideoCost = Db::name('user_setting')->where('user_id', $param['accept_uid'])->value('video_cost');
                if ($zhuboVideoCost && $zhuboVideoCost * 5 > $userInfo['coin']) {
                    $this->success("OK", [
                        'code' => 201,
                        'is_msg' => 0,
                        'is_msg_err' => '金币不足5分钟通话时长',
                        'is_voice' => 0,
                        'is_video' => 0,
                        'is_voice_callback' => 0,
                        'is_video_callback' => 0,
                    ]);
                }
            }

//            if ($userInfo['daren_status'] == 2) {
//                // 触发者是主播
//                $zhuboVideoCost = Db::name('user_setting')->where('user_id', $userId)->value('video_cost');
//                if ($zhuboVideoCost && $zhuboVideoCost * 5 > $acceptUserInfo['coin']) {
//                    $this->success("OK", [
//                        'is_msg' => 0,
//                        'is_msg_err' => '金币不足5分钟通话时长',
//                        'is_voice' => 0,
//                        'is_video' => 0,
//                        'is_voice_callback' => 0,
//                        'is_video_callback' => 0,
//                    ]);
//                }
//            }

            $this->success("OK", [
                'code' => 0,
                'is_msg' => 1,
                'is_msg_err' => '',
                'is_voice' => 1,
                'is_video' => 1,
                'is_voice_callback' => 1,
                'is_video_callback' => 1,
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 发起聊天，
     *   检测发起者状态，是否vip，剩余金币，
     *   检测接受者状态，是否设置聊天付费，
     *   计算发起者剩余聊天时间
     *   创建聊天订单
     */
    public function launchChat()
    {
        try {
            $validate = new Validate([
                'accept_uid' => 'require|integer', // 聊天对象uid
                'robot_id' => 'require|integer', // 机器人id ，如果非机器人，则该值与accept_uid相同
                'type' => 'require|in:1,2,3' // 聊天类型（1:文字 2:语音 3:视频）
            ]);

            $validate->message([
                'accept_uid.require' => '请输入聊天对象用户id!',
                'type.require' => '请输入聊天类型!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            // 客服
            if (in_array($param['type'], [2, 3])) {
                $customerOption = cmf_get_option('customer_config');
                $uids = $customerOption['customer_config']['customer']['uid'];
                $aKfUid = explode(',', $uids);
                if (in_array($userId, $aKfUid) || in_array($param['accept_uid'], $aKfUid)) {
                    $this->error('客服不能音视频聊天');
                }
            }

            $acceptUserInfo = Db::name("user")->find($param['accept_uid']);
            if (! $acceptUserInfo) {
                $this->error('聊天对象参数非法');
            }
            if ($acceptUserInfo['daren_status'] != 2) {
                $this->error('该用户不是主播，不能音视频聊天');
            }

            $userInfo = Db::name("user")->find($userId);
            if ($userInfo['daren_status'] == 2) {
                $this->error('主播与主播不能音视频聊天');
            }

            // 用户等级控制
            $isVip = VipModule::checkIsVip($userInfo['vip_expire_time']);
            if (! $isVip) {
                $userLevel = UserModule::getUserLevelByUid($userId); // 用户财富等级
                $publicConfig = cmf_get_option('public_config');
                $levelLimit = $publicConfig['public_config']['UserLevelLimit'];
                if ($param['type'] == 2) {
                    if (!empty($levelLimit['voice']) && $userLevel < $levelLimit['voice']) {
                        $this->error('您的等级不够');
                    }
                } elseif ($param['type'] == 3) {
                    if (!empty($levelLimit['video']) && $userLevel < $levelLimit['video']) {
                        $this->error('您的等级不够');
                    }
                }
            }

            # 获取设置
            $acceptUserSettingRow = Db::name("user_setting")->where('user_id', $param['robot_id'])->find();
            $open_video = isset($acceptUserSettingRow['open_video']) ? $acceptUserSettingRow['open_video'] : 0;
            $video_cost = isset($acceptUserSettingRow['video_cost']) ? $acceptUserSettingRow['video_cost'] : 0;
            $open_speech = isset($acceptUserSettingRow['open_speech']) ? $acceptUserSettingRow['open_speech'] : 0;
            $speech_cost = isset($acceptUserSettingRow['speech_cost']) ? $acceptUserSettingRow['speech_cost'] : 0;

            if ($param['type'] == 2 && $open_speech == 0) {
                $this->error('对方暂时无法接听');
            }
            if ($param['type'] == 3 && $open_video == 0) {
                $this->error('对方暂时无法接听');
            }

            if ($param['robot_id'] == $param['accept_uid']) {
                $param['robot_id'] = 0;
            } else {
                if (! Db::name('user')->where(['id' => $param['robot_id'], 'user_type' => 3])->count()) {
                    $this->error('虚拟参数非法');
                }
            }

            # 创建聊天订单
            $result = ChatModule::launchChat($userId, $param['accept_uid'], $param['type'], $param['robot_id']);

            if ($result) {
                $this->success("OK", [
                    'order_no' => $result['order_no'], // 聊天订单号
                    'home_id' => $result['home_id'], // 视频聊天房间号
                    'rest_time' => $result['rest_time'], // 剩余聊天时间，单位分钟
                    'open_video' => $open_video,
                    'video_cost' => $video_cost,
                    'open_speech' => $open_speech,
                    'speech_cost' => $speech_cost,
                ]);
            } else {
                if (ChatModule::$errCode == 201) {
                    $this->error(['code' => 201, 'msg' => '金币不足1分钟通话时长']);
                }
                $this->error(ChatModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 更新聊天时长，每分钟更新一次
     *   检测聊天订单数据是否合法
     *   检测发起者金币是否满足一个单位时间的费用
     *   更新订单时间 和 金币花费
     *   更新发起者金币余额
     */
    public function updChatDurationPerMin()
    {
        try {
            $validate = new Validate([
                'order_no' => 'require', // 聊天订单号
                'duration_time' => 'require|integer' // 聊天持续时间，单位分钟，需要和服务端存储的时间对比
            ]);

            $validate->message([
                'order_no.require' => '请输入订单号!',
                'duration_time.require' => '请输入聊天持续时间!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            # 更新时长
            $restTime = ChatModule::updChatDurationPerMin($param['order_no'], $param['duration_time']);
            if ($restTime !== false) {
                $this->success("OK", ['rest_time' => $restTime]);
            } else {
                $this->error("更新失败," . ChatModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 关闭聊天
     */
    public function closeChat()
    {
        try {
            $validate = new Validate([
                'order_no' => 'require', // 聊天订单号
            ]);

            $validate->message([
                'order_no.require' => '请输入订单号!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            # 关闭聊天订单
            $result = ChatModule::closeChat($param['order_no']);

            if ($result !== false) {
                $this->success("OK");
            } else {
                $this->error("关闭失败," . ChatModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 创建求聊任务
     */
    public function publishChatTask()
    {
        try {
            $validate = new Validate([
                'per_cost' => 'require|integer|min:1', // 每分钟计费 单位金币
                'type' => 'require|in:1,2,3' // 聊天类型（1:文字 2:语音 3:视频）
            ]);

            $validate->message([
                'per_cost.require' => '请输入计费标准!',
                'type.require' => '请输入聊天类型!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $userRow = Db::name("user")->find($userId);
            if ($userRow['coin'] / $param['per_cost'] < 5) { // 当前剩余金币需要满足最低5分钟的聊天费用
                $this->error(['code'=>201, 'msg'=>\dctool\Cgf::getCoinNickname() . '不足，至少满足5分钟']);
            }

            #检测该用户当前是否存在进行中的任务
            if (Db::name('chat_task')->where("user_id={$userId} AND type={$param['type']} AND status < 2 AND status > 0")->count()) {
                $this->error('您当前存在未结束的任务');
            }

            # 创建求聊任务
            $insTask = [
                'type'       => $param['type'],
                'user_id'    => $userId,
                'per_cost'   => $param['per_cost'],
                'status'     => 1, //任务发布默认为成功，即可在任务大厅中显示
                'create_time'=> time(),
                'update_time'=> time(),
            ];

            if (Db::name('chat_task')->insert($insTask)) {
                $this->success("OK");
            } else {
                $this->error("创建失败");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 取消求聊任务
     */
    public function cancelChatTask()
    {
        try {
            $validate = new Validate([
                'task_id' => 'require|integer', // 任务id
            ]);

            $validate->message([
                'task_id.require' => '请输入任务id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            $taskRow = Db::name('chat_task')->where("user_id={$userId} AND id={$param['task_id']}")->find();

            if (empty($taskRow)) {
                $this->error('该任务不存在！');
            }

            if ($taskRow['status'] > 1) {
                $this->error('该任务不能取消，操作非法！');
            }

            if (Db::name('chat_task')->where("user_id={$userId} AND id={$param['task_id']} AND status=1")->delete()) {
                $this->success("OK");
            } else {
                $this->error("取消失败");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 任务列表
     */
    public function chatTaskList()
    {
        try {
            $validate = new Validate([
                'page' => 'require|integer|min:1',
                'type' => 'require|in:1,2,3' // 聊天类型（1:文字 2:语音 3:视频）
            ]);

            $validate->message([
                'page.require' => '请输入页码!',
                'type.require' => '请输入聊天类型!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;
            $userId = $this->getUserId();

            // 获取自己的任务
            if ($iPage == 1) {
                $myTaskRow = Db::name('chat_task')
                    ->alias('t')
                    ->join('user u', 'u.id = t.user_id')
                    ->where("t.status = 1")
                    ->where("t.is_lock = 0")
                    ->where("t.type = {$param['type']}")
                    ->where('t.user_id = ' . $userId)
                    ->field('t.*,u.avatar,u.user_nickname,u.vip_expire_time')
                    ->find();
                if ($myTaskRow) {
                    $myTask = [
                        'user_id' => $myTaskRow['user_id'],
                        'prom_custom_uid' => $myTaskRow['user_id'],
                        'user_nickname' => $myTaskRow['user_nickname'],
                        'is_vip' => VipModule::checkIsVip($myTaskRow['vip_expire_time']), // 是否vip (1:是 0:否)
                        'task_id' => $myTaskRow['id'],
                        'avatar' => MaterialModule::getFullUrl($myTaskRow['avatar']),
                        'per_cost' => $myTaskRow['per_cost'],
                        'publish_time' => ChatModule::timeTran($myTaskRow['create_time'])
                    ];
                }
            } else {
                $myTask = [];
            }

            $result = Db::name('chat_task')
                ->alias('t')
                ->join('user u', 'u.id = t.user_id')
                ->where("t.status = 1")
                ->where("t.is_lock = 0")
                ->where("t.type = {$param['type']}")
                ->where('t.user_id <> ' . $userId)
                ->field('t.*,u.avatar,u.user_nickname,u.vip_expire_time')
                ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
                ->toArray();

            $aRet = [];
            foreach ($result['data'] as $row) {
                $aRet[] = [
                    'user_id' => $row['user_id'],
                    'prom_custom_uid' => $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
                    'task_id' => $row['id'],
                    'avatar' => MaterialModule::getFullUrl($row['avatar']),
                    'per_cost' => $row['per_cost'],
                    'publish_time' => ChatModule::timeTran($row['create_time'])
                ];
            }

            if (empty($myTask)) {
                $list = $aRet;
            } else {
                $list = array_merge([$myTask], $aRet);
            }

            $this->success("OK", ['list' => $list, 'total_page' => $result['last_page']]);
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接聊天任务
     *   检测发起者状态，是否vip，剩余金币，
     *   检测接受者状态，是否设置聊天付费，
     *   计算发起者剩余聊天时间
     *   创建聊天订单
     */
    public function getChatTask()
    {
        try {
            $validate = new Validate([
                'task_id' => 'require|integer', // 任务id
            ]);

            $validate->message([
                'task_id.require' => '请输入任务id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $userRow = Db::name('user')->find($userId);
            if ($userRow['daren_status'] != 2) {
                $this->error('您还不是主播，不能接任务哦');
            }

            $taskRow = Db::name('chat_task')->find($param['task_id']);
            if (! $taskRow) {
                $this->error("没有该任务");
            } elseif ($taskRow['status'] != 1 || $taskRow['is_lock'] != 0) {
                $this->error("该任务已经被抢走了");
            } elseif ($userId == $taskRow['user_id']) {
                $this->error("不能接自己的任务");
            }

            // 任务加锁，加锁后即不能被操作
            Db::name('chat_task')->where(['id'=>$param['task_id'],'status'=>1])->update(['is_lock'=>1]);

            # 创建聊天订单
            $result = ChatModule::getChatTask($userId, $param['task_id']);

            if ($result) {
                $this->success("OK", [
                    'order_no' => $result['order_no'], // 聊天订单号
                    'home_id'  => $result['home_id'], // 视频聊天房间号
                    'rest_time' => $result['rest_time'], // 剩余聊天时间，单位分钟
                ]);
            } else {
                $this->error("发起聊天失败," . ChatModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取新注册的异性用户
     *   获取与登录用户性别相反的最新注册的用户
     */
    public function getNewHeterosexuUsers()
    {
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
            $iPageSize = 15;
            $userId = $this->getUserId();
            $lastRegisterTime = time() - 3600*24*3; // 新注册时间限制 24*3 小时前

            $isCustom = Db::name('role_user')->where('user_id', $userId)->where('role_id', 3)->count();
            if ($isCustom) {
                // 是客服，取所有性别的新用户
                $result = Db::name('user')
                    ->alias('u')
                    ->where('u.id <> ' . $userId)
                    ->where('u.user_type = 2')
                    ->where('u.info_complete = 1')
                    ->where('u.create_time > ' . $lastRegisterTime)
                    ->field('u.*')
                    ->order('u.id', 'desc') // 按注册时间倒序
                    ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
                    ->toArray();
            } else {
                //是普通用户，取与登录用户性别相反的新用户
                if ($this->userSex == 1) {
                    $whereSex = 'u.sex = 2';
                } elseif ($this->userSex == 2) {
                    $whereSex = 'u.sex = 1';
                } else {
                    $whereSex = '';
                }
                $result = Db::name('user')
                    ->alias('u')
                    ->where('u.id <> ' . $userId)
                    ->where('u.user_type = 2')
                    ->where('u.info_complete = 1')
                    ->where($whereSex)
                    ->where('u.create_time > ' . $lastRegisterTime)
                    ->field('u.*')
                    ->order('u.id', 'desc') // 按注册时间倒序
                    ->paginate($iPageSize, false, ['page'=>$iPage, 'list_rows'=>$iPageSize])
                    ->toArray();
            }

            $aRet = [];
            foreach ($result['data'] as $row) {
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
                    'signature' => $row['signature'],
                    'sex' => $row['sex'],
                    'age' => $row['age'],
                    'province_name' => $row['province_name'],
                    'city_name' => $row['city_name'],
                    'district_name' => $row['district_name'],
                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                    'register_time' => ChatModule::timeTran($row['create_time']),
                ];
            }

            $this->success("OK", [
                'total_page' => $result['last_page'],
                'list' => $aRet,
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error("fail，" . $e->getMessage());
        }
    }

    /**
     * 匹配聊天
     *   随机找人聊天，达人，性别相反
     */
    public function findRandomUser()
    {
        $this->getUserId();

        try {
            if ($this->userSex == 2) {
                $sex = 1;
            } else {
                $sex = 2;
            }

            $findUserIds = Db::name('user')->alias('u')
                ->join('user_token t', 't.user_id=u.id')
//                ->where('t.last_online_time >=' . (time()-600))
                ->where('u.sex', $sex)
                ->where('u.user_status', 1)
                ->where('u.daren_status', 2)
                ->whereIn('user_type', [2, 3])
                ->column('u.id');
            if (! empty($findUserIds)) {
                $findUId = $findUserIds[array_rand($findUserIds)];
            } else {
                $this->error('没有找到合适的用户');
            }

            // 获取匹配到的用户信息
            $userFind = Db::name('user')->find($findUId);

            // 获取用户的运营客服，如果不是机器人，则该值就是用户的id
            if ($userFind['user_type'] == 3) {
                $promCustomUid = Db::name('allot_robot')->where('robot_id', $userFind['id'])->value('custom_id');
            }

            $this->success("OK", [
                'info' => [
                    'user_id' => $userFind['id'],
                    'user_nickname' => $userFind['user_nickname'],
                    'sex' => $userFind['sex'],
                    'age' => $userFind['age'],
                    'hometown' => $userFind['city_name'],
                    'avatar' => $userFind['avatar'],
                    'prom_custom_uid' => isset($promCustomUid) ? $promCustomUid : $userFind['id']
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
     * 获取新注册用户户--客服端专用
     *   客服看不到其他业务员邀请过来的新用户
     */
//    public function getNewUsers4Custom()
//    {
//        try {
//            $validate = new Validate([
//                'page' => 'require|min:1'
//            ]);
//
//            $validate->message([
//                'page.require' => '请输入当前页!'
//            ]);
//
//            $param = $this->request->param();
//            if (!$validate->check($param)) {
//                $this->error($validate->getError());
//            }
//
//            $iPage = $param['page'];
//            $iPageSize = 15;
//            $userId = $this->getUserId();
//            $lastRegisterTime = time() - 3600 * 24 * 3; // 新注册时间限制 24*3 小时前
//
//            // 过滤掉不属于自己邀请的用户id
//            $notUids = Db::name('user_invite_revel')
//                ->where('invite_user_id != ' . $userId)
//                ->where("create_time >= {$lastRegisterTime}")
//                ->column('beinvite_user_id');
//
//            $merchantId = Db::name('merchant_customer')->where('user_id', $userId)->value('m_id');
//
//            if ($merchantId) {
//                // 该客服有归属商户
//                $result = Db::name('user')
//                    ->alias('u')
//                    ->join('merchant_allot_user m', 'm.user_id = u.id AND m.merchant_id = ' . $merchantId)
//                    ->where('u.id <> ' . $userId)
//                    ->where('u.user_type = 2')
//                    ->where('u.info_complete = 1')
//                    ->where('u.create_time > ' . $lastRegisterTime)
//                    ->where('u.id', 'NOTIN', $notUids)
//                    ->field('u.*')
//                    ->order('u.id', 'desc')// 按注册时间倒序
//                    ->paginate($iPageSize, false, ['page' => $iPage, 'list_rows' => $iPageSize])
//                    ->toArray();
//            } else {
//                // 该客服不归属任何商户
//                $result = Db::name('user')
//                    ->alias('u')
//                    ->where('u.id <> ' . $userId)
//                    ->where('u.user_type = 2')
//                    ->where('u.info_complete = 1')
//                    ->where('u.create_time > ' . $lastRegisterTime)
//                    ->where('u.id', 'NOTIN', $notUids)
//                    ->field('u.*')
//                    ->order('u.id', 'desc')// 按注册时间倒序
//                    ->paginate($iPageSize, false, ['page' => $iPage, 'list_rows' => $iPageSize])
//                    ->toArray();
//            }
//
//            if (! $result) {
//                $this->error('数据为空');
//            }
//            $aRet = [];
//            foreach ($result['data'] as $row) {
//                // 获取该用户归属的客服信息
//                $customRow = Db::name('prom_invite_rela')->alias('r')
//                    ->join('user u', 'u.id = r._id')
//                    ->where('r.user_id', $row['id'])
//                    ->field('u.id, u.user_nickname, u.avatar')
//                    ->find();
//
//                $aRet[] = [
//                    'user_id' => $row['id'],
//                    'user_nickname' => $row['user_nickname'],
//                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']), // 是否vip (1:是 0:否)
//                    'signature' => $row['signature'],
//                    'sex' => $row['sex'],
//                    'age' => $row['age'],
//                    'province_name' => $row['province_name'],
//                    'city_name' => $row['city_name'],
//                    'district_name' => $row['district_name'],
//                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
//                    'register_time' => ChatModule::timeTran($row['create_time']),
//                    'attribute_custom_user_id' => !empty($customRow['id']) ? $customRow['id'] : 0,
//                    'attribute_custom_user_nickname' => !empty($customRow['user_nickname']) ? $customRow['user_nickname'] : '',
//                    'attribute_custom_user_avatar' => !empty($customRow['avatar']) ? MaterialModule::getFullUrl($customRow['avatar']) : '',
//                ];
//            }
//
//            $this->success("OK", [
//                'total_page' => $result['last_page'],
//                'list' => $aRet,
//            ]);
//
//        } catch (Exception $e) {
//            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
//
//            $this->error("fail，" . $e->getMessage());
//        }
//    }
}
