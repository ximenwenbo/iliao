<?php

namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\UserModule;
use api\app\module\ChatModule;
use api\app\module\FandefModule;
use api\app\module\MaterialModule;
use api\app\module\ConfigModule;

/**
 * 范-定制
 */
class FandefController extends RestBaseController
{
    /**
     * 主播星级列表
     */
    public function starUserList()
    {
        try {
            $validate = new Validate([
                'level' => 'require|integer|in:1,2,3,4,5',
                'page' => 'require|integer|min:1'
            ]);

            $validate->message([
                'level.require' => '请输入等级!',
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;
            $aVideoCoin = $this->getStarCoin($param['level']);

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            // 获取客服uid
            $customUids = Db::name('role_user')->where('role_id', 3)->column('user_id');

            $subQuery1 = Db::name('user')->alias('u')
                ->join('user_setting s', 's.user_id = u.id AND s.video_cost >= '.$aVideoCoin['min'].' AND s.video_cost <= '.$aVideoCoin['max'])
                ->join('user_token t', 't.user_id = u.id')
                ->whereIn('t.online_status', [1,2,3,4,5])
                ->where('u.user_type', 2)
                ->where('u.virtual_pos', 0)
                ->where('u.user_status', 1)
                ->where('u.daren_status',2)
                ->whereNotIn('u.id', $aBlockedUid)
                ->whereNotIn('u.id', $customUids)
                ->field("u.*,(u.withdraw_coin+u.withdraw_frozen_coin+u.withdraw_used_coin) as total_coin,
                s.video_cost,s.speech_cost,t.online_status,
                case when (t.online_status=1) then 10
                     when (t.online_status=2) then 9
                     when (t.online_status=4) then 8
                     when (t.online_status=3) then 7
                     when (t.online_status=5) then 6
                else 0 end as sor1")
                ->buildSql();

            $subQuery2 = Db::name('user')->alias('u')
                ->join('user_setting s', 's.user_id = u.id AND s.video_cost >= '.$aVideoCoin['min'].' AND s.video_cost <= '.$aVideoCoin['max'])
                ->join('user_token t', 't.user_id = u.id')
                ->whereIn('t.online_status', [0])
                ->where('u.user_type', 2)
                ->where('u.virtual_pos', 0)
                ->where('u.user_status', 1)
                ->where('u.daren_status',2)
                ->whereNotIn('u.id', $aBlockedUid)
                ->whereNotIn('u.id', $customUids)
                ->field("u.*,(u.withdraw_coin+u.withdraw_frozen_coin+u.withdraw_used_coin) as total_coin,s.video_cost,s.speech_cost,t.online_status,case when (t.online_status>0) then 1 else 0 end as sor1")
                ->buildSql();

            $result = Db::table(" {$subQuery1} as lis union all {$subQuery2}")
                ->order('sor1', 'desc')
                ->order('total_coin', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
//            var_dump(Db::name('user')->getLastSql());die;
//            var_dump($result);die;

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
                    'online_state' => $row['online_status'],
                    'sor1' => $row['sor1'],
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
                'list' => $aRet,
                'banner' => $bannerList,
                'show_style' => 1
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取星级金币数
     * @param $level
     * @return int|mixed
     */
    private function getStarCoin($level)
    {
        $anchorLevel = cmf_get_option('anchor_level');
        $AnchorLevel = $anchorLevel['AnchorLevel'];
        $star = explode(',', $AnchorLevel['star']); // 2
        $model = explode(',', $AnchorLevel['model']); // 3
        $goddess = explode(',', $AnchorLevel['goddess']); // 4
        $queen = explode(',', $AnchorLevel['queen']); // 5
        $list = [
            '2' => [
                'min' => $star[0],
                'max' => $star[1],
            ],
            '3' => [
                'min' => $model[0],
                'max' => $model[1],
            ],
            '4' => [
                'min' => $goddess[0],
                'max' => $goddess[1],
            ],
            '5' => [
                'min' => $queen[0],
                'max' => $queen[1],
            ],
        ];

        return isset($list[$level]) ? $list[$level] : ['min'=>0, 'max'=>0];
    }

    /**
     * 获取用户是否主播
     */
    public function getZhuboState()
    {
        $userId = $this->getUserId();

        try {
            $darenStatus = Db::name("user")->where('id', $userId)->value('daren_status');

            $this->success("OK", [
                'is_zhubo' => $darenStatus == 2 ? 1 : 0, // 是否是主播
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 主播向用户发起聊天，扣用户的钱
     */
    public function launchChatFromZhubo()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'callback_uid' => 'require|integer', // 回拨对象uid
                'type' => 'require|in:2,3' // 聊天类型（2:语音 3:视频）
            ]);

            $validate->message([
                'callback_uid.require' => '请输入回拨对象用户id!',
                'type.require' => '请输入聊天类型!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userInfo = Db::name("user")->find($userId);
            if ($userInfo['daren_status'] != 2) {
                $this->error('主播才能回拨');
            }

            $callbackUserInfo = Db::name("user")->find($param['callback_uid']);
            if (! $callbackUserInfo) {
                $this->error('回拨对象参数非法');
            }
            if ($callbackUserInfo['daren_status'] == 2) {
                $this->error('主播与主播不能音视频聊天');
            }

            /**
            // 用户等级控制
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
             * */

            # 获取设置
            $userSettingRow = Db::name("user_setting")->where('user_id', $userId)->find();
            $open_video = isset($userSettingRow['open_video']) ? $userSettingRow['open_video'] : 0;
            $video_cost = isset($userSettingRow['video_cost']) ? $userSettingRow['video_cost'] : 0;
            $open_speech = isset($userSettingRow['open_speech']) ? $userSettingRow['open_speech'] : 0;
            $speech_cost = isset($userSettingRow['speech_cost']) ? $userSettingRow['speech_cost'] : 0;

//            if ($param['type'] == 2 && $open_speech == 0) {
//                $this->error('您还未开启语音通话');
//            }
//            if ($param['type'] == 3 && $open_video == 0) {
//                $this->error('您还未开启视频通话');
//            }

            $callBackUserSettingRow = Db::name("user_setting")->where('user_id', $param['callback_uid'])->find();
            $callback_open_speech = isset($callBackUserSettingRow['open_speech']) ? $callBackUserSettingRow['open_speech'] : 0;
            $callback_open_video = isset($callBackUserSettingRow['open_video']) ? $callBackUserSettingRow['open_video'] : 0;
            if ($param['type'] == 2 && $callback_open_speech == 0) {
                $this->error('用户还未开启语音通话');
            }
            if ($param['type'] == 3 && $callback_open_video == 0) {
                $this->error('用户还未开启视频通话');
            }

            # 创建聊天订单
            $result = ChatModule::launchChat($param['callback_uid'], $userId, $param['type']);

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
                    $this->error( '回拨用户金币不足1分钟通话时长');
                }
                $this->error(ChatModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取客服列表
     */
    public function getKfList()
    {
        $this->getUserId();

        try {
            $customerOption = cmf_get_option('customer_config');
            $uids = $customerOption['customer_config']['customer']['uid'];

            $kfUids = explode(',', $uids);
            if (empty($kfUids)) {
                $this->success("OK", [
                    'list' => [],
                    'extra' => [
                        'object_domain' => ConfigModule::getObjectDomain(),
                    ]
                ]);
            }

            $kfSelect = Db::name('user')->whereIn('id', $kfUids)->field('id,user_nickname,avatar')->select();

            $list = [];
            foreach ($kfSelect as $item) {
                $list[] = [
                    'subject' => '客服：' . $item['user_nickname'],
                    'user_id' => $item['id'],
                    'user_nickname' => $item['user_nickname'],
                    'avatar' => $item['avatar'],
                ];
            }

            $this->success("OK", [
                'list' => $list,
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
     * 获取房间沙发列表
     */
    public function getVoiceSofaList()
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

            $liveId = Db::name('live_home')->where('user_id', $param['user_id'])->order('id', 'desc')->value('id');
            // 当前在线的观众
            $aViewerUid = Db::name('live_home_viewer')->where(['live_id'=>$liveId, 'user_type'=>2, 'status'=>1])->column('user_id');

            $result = Db::name('gift_given_order')
                ->alias('o')
                ->join('user u', 'u.id = o.send_uid')
                ->where('o.send_time', '>', strtotime(date('Ymd')))
                ->where('o.receive_uid', $param['user_id'])
                ->whereIn('u.id', $aViewerUid)
                ->group('o.send_uid')
                ->field('u.id user_id,u.user_nickname,u.avatar,SUM(o.total_coin) as total')
                ->order('total', 'desc')
                ->limit(4)
                ->select()
                ->toArray();

            $liveFind = Db::name('live_home')->where(['user_id' => $param['user_id'], 'status' => 1])->order('id', 'desc')->find();
            if (! $liveFind) {
                foreach ($result as &$item) {
                    $item['is_lianmai'] = 0;
                }
            } else {
                foreach ($result as &$item) {
                    $streamStatus = Db::name('live_stream')
                        ->where(['option_id' => $liveFind['id'], 'option_class_id' => 22, 'user_id' => $item['user_id']])
                        ->order('id', 'desc')
                        ->value('status');

                    if ($streamStatus == 1) {
                        $item['is_lianmai'] = 1;
                    } else {
                        $item['is_lianmai'] = 0;
                    }
                }
            }

            $this->success("OK", [
                'list' => $result,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()), 'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 申请语音连麦（观众发起）
     */
    public function applyVoiceLianmai()
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
            $tLiveUrl = FandefModule::createPushStream($userId, $streamId, ['option_type'=>2, 'option_id'=>$liveFind['id'], 'option_class_id'=>22], 2);

            $this->success("OK", [
                't_live_url' => $tLiveUrl, // 推流地址
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
