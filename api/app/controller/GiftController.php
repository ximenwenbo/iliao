<?php
/**
 * User: coase
 * Date: 2019-01-08
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\GiftModule;
use api\app\module\MaterialModule;

/**
 * #####礼物的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.获取礼物列表
 * 2.赠送礼物
 * 3.获取用户获增的礼物列表
 * ``````````````````
 */
class GiftController extends RestBaseController
{
    /**
     * 获取礼物列表
     */
    public function getGiftList()
    {
        try {
            $validate = new Validate([
                'type' => 'integer|in:1,2', // 场景类型id (1:社区聊天礼物 2:直播礼物）
            ]);

            $validate->message([
                'type.integer' => '参数类型错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            $type = isset($param['type']) ? intval($param['type']) : 1;

            $giftResult = Db::name('gift')
                ->where('type', $type)
                ->where('status', 1)
                ->order('sort', 'asc')
                ->select();

            if (empty($giftResult)) {
                $this->success();
            }

            $giftList = [];
            foreach ($giftResult as $item) {
                $giftList[] = [
                    'gift_uni_code' => $item['uni_code'],
                    'name' => $item['name'],
                    'icon_img' => MaterialModule::getFullUrl($item['icon_img']),
                    'effect_img' => MaterialModule::getFullUrl($item['effect_img']),
                    'style' => $item['style'], // 礼物样式 1:图片 2:gif动图 3:svga动图
                    'ch_cat_id' => $item['ch_cat_id'], // 小分类id
                    'coin' => $item['coin'],
                    'coin_zh' => $item['coin'] . \dctool\Cgf::getCoinNickname(),
                    'tags' => $item['tags']
                ];
            }

            $this->success("OK", [
                'list' => $giftList,
                'rest_coin' => Db::name('user')->where('id', $this->userId)->value('coin') ? : 0
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 赠送礼物
     *   检测发起者状态，剩余金币，
     *   检测接受者状态
     */
    public function sendGift()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'receive_uid' => 'require|integer', // 接收者uid
                'robot_id' => 'require|integer', // 机器人id ，如果非机器人，则该值与receive_uid相同
                'gift_uni_code' => 'require', // 礼物唯一码
                'gift_num' => 'integer|min:1', // 礼物数量，不传默认为1
            ]);

            $validate->message([
                'receive_uid.require' => '请输入接收者uid!',
                'gift_uni_code.require' => '请输入礼物码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $param['gift_num'] = isset($param['gift_num']) ? $param['gift_num'] : 1;

            $receiveUserInfo = Db::name("user")->find($param['receive_uid']);
            if (! $receiveUserInfo) {
                $this->error('接收者参数非法');
            }

            $giftInfo = Db::name("gift")->where('uni_code', $param['gift_uni_code'])->find();
            if (! $giftInfo) {
                $this->error('礼物参数非法');
            }

            # 如果有机器人，则礼物接收者改为机器人
            if ($param['robot_id'] == $param['receive_uid']) {
                $receiveUid = $param['receive_uid'];
            } else {
                if (! Db::name('user')->where(['id' => $param['robot_id'], 'user_type' => 3])->count()) {
                    $this->error('机器人参数非法');
                }
                $receiveUid = $param['robot_id'];
            }

            # 赠送礼物
            $result = GiftModule::sendGift($userId, $receiveUid, $param['gift_uni_code'], $param['gift_num']);

            if ($result) {
                // 礼物消息入库
                /**
                $jobHandlerClassName = 'app\admin\job\GiftMsgJob'; // 当前任务将由哪个类来负责处理。
                $jobQueueName = 'bullet_screen_gift_msg'; // 当前任务归属的队列名称,如果为新队列，会自动创建(bullet_screen_gift_msg:礼物消息弹幕)
                $jobData = [ // 当前任务所需的业务数据,不能为 resource 类型，其他类型最终将转化为json形式的字符串(jobData 为对象时，存储其public属性的键值对)
                    'from_uid' => $userId, // 发送者id
                    'to_uid' => $receiveUid, // 接收者id
                    'create_time' => time(),
                    'gift_uni_code' => $param['gift_uni_code'],
                    'type' => 'all_user', // 给所有用户发送
                    'bizId' => uniqid()
                ];
                $isPushed = \think\Queue::push($jobHandlerClassName , $jobData , $jobQueueName); // 将该任务推送到消息队列，等待对应的消费者去执行
                if ($isPushed === false) {
                    Log::write(sprintf('%s：礼物消息插入队列错误，返回:%s', __METHOD__, var_export($isPushed, true)),'error');
                }
                 **/

                // 礼物消息2 入库，当礼物价值超过配置数时才发送
                $publicConfig = cmf_get_option('public_config');
                if ($giftInfo['coin'] * $param['gift_num'] >= $publicConfig['public_config']['GiftNotice']['coin']) {
                    $jobHandlerClassName = 'app\admin\job\GiftMsgJob2'; // 当前任务将由哪个类来负责处理。
                    $jobQueueName = 'live_bullet_screen_gift_msg'; // 当前任务归属的队列名称,如果为新队列，会自动创建(live_bullet_screen_gift_msg:直播礼物消息弹幕)
                    $jobData = [ // 当前任务所需的业务数据,不能为 resource 类型，其他类型最终将转化为json形式的字符串(jobData 为对象时，存储其public属性的键值对)
                        'from_uid' => $userId, // 发送者id
                        'to_uid' => $receiveUid, // 接收者id
                        'create_time' => time(),
                        'gift_uni_code' => $param['gift_uni_code'],
                        'gift_num' => $param['gift_num'],
                        'type' => 'all_viewer', // 给所有观看直播的用户发送
                        'bizId' => uniqid()
                    ];
                    $isPushed = \think\Queue::push($jobHandlerClassName, $jobData, $jobQueueName); // 将该任务推送到消息队列，等待对应的消费者去执行
                    if ($isPushed === false) {
                        Log::write(sprintf('%s：礼物消息插入队列错误，返回:%s', __METHOD__, var_export($isPushed, true)), 'error');
                    }
                }

                $this->success("OK");
            } else {
                $this->error(['code' => GiftModule::$errCode, 'msg' => GiftModule::$errMessage]);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取用户获增的礼物列表
     */
    public function getReceiveGifts()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer',
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $giftResult = Db::name('gift_given_order')->alias('o')
                ->join('gift g', 'g.uni_code = o.gift_uni_code')
                ->where(['o.receive_uid' => $param['user_id']])
                ->group('o.gift_uni_code')
                ->field('g.name,g.icon_img,g.effect_img,o.gift_uni_code,sum(o.num) total')
                ->select();

            $giftList = [];
            foreach ($giftResult as $item) {
                $giftList[] = [
                    'gift_uni_code' => $item['gift_uni_code'],
                    'name' => $item['name'],
                    'icon_img' => MaterialModule::getFullUrl($item['icon_img']),
                    'effect_img' => MaterialModule::getFullUrl($item['effect_img']),
                    'total' => $item['total'],
                ];
            }

            $this->success("OK", $giftList);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
