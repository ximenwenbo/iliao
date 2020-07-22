<?php
/**
 * User: coase
 * Date: 2019-06-18
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\UserModule;
use api\app\module\WatchModule;
use api\app\module\ConfigModule;

/**
 * #####守护的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.获取礼物列表
 * 2.赠送礼物
 * 3.获取用户获增的礼物列表
 * ``````````````````
 */
class WatchController extends RestBaseController
{
    /**
     * 获取守护资费列表
     */
    public function getWatchTypeList()
    {
        $userId = $this->getUserId();
        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播uid
            ]);

            $validate->message([
                'user_id.require' => '请输入主播uid!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userRow = Db::name('user')->find($userId);

            $watchExpireTime = Db::name('watch_relation')->where(['user_id' => $userId, 'live_user_id' => $param['user_id']])->value('watch_expire_time');
            if (! $watchExpireTime) {
                $watch_expire_time = '未开通';
            } elseif ($watchExpireTime < strtotime(date('Ymd', time()))) {
                $watch_expire_time = '已过期';
            } else {
                $watch_expire_time = date('Y-m-d', $userRow['vip_expire_time']);
            }

            $aTypeList = WatchModule::getWatchTypeList($userId);

            $this->success("OK", [
                'watch_expire_time' => $watch_expire_time,
                'rest_coin' => $userRow['coin'],
                'list' => array_values($aTypeList)
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 赠送守护
     */
    public function addWatch()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'receive_uid' => 'require|integer', // 被守护用户uid
                'day_time' => 'require|integer|in:7,30,365' // 守护时间,天
            ]);

            $validate->message([
                'receive_uid.require' => '请输入用户uid!',
                'day_time.require' => '请输入守护时间!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $receiveUserInfo = Db::name("user")->find($param['receive_uid']);
            if (! $receiveUserInfo) {
                $this->error('接收者参数非法');
            }

            if (WatchModule::addWatch($userId, $param['receive_uid'], $param['day_time'])) {
                $this->success("OK");
            } else {
                $this->error(['code' => WatchModule::$errCode, 'msg' => WatchModule::$errMessage]);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取守护主播的用户列表
     */
    public function getWatchedUserByLiveUid()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 主播uid
                'page' => 'require|min:1'
            ]);

            $validate->message([
                'user_id.require' => '请输入主播uid!',
                'page.require' => '请输入当前页!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            // 获取守护用户
            $result = Db::name('watch_relation')->alias('r')
                ->join('user u', 'u.id = r.user_id')
                ->where('r.live_user_id', $param['user_id'])
                ->where('r.watch_expire_time > ' . time())
                ->field('u.id,u.user_nickname,u.avatar,u.sex,u.vip_expire_time')
                ->paginate($iPageSize, false, ['page' => $iPage, 'list_rows' => $iPageSize])
                ->toArray();

            if (! $result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result['data'] as $row) {
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'avatar' => $row['avatar'],
                    'sex' => $row['sex'],
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'user_level' => 'LV' . UserModule::getUserLevelByUid($row['id']), // 用户财富等级
                    'cost_coin' => Db::name('watch_order')
                        ->where(['send_uid' => $row['id'], 'receive_uid' => $param['user_id'], 'status' => 1])
                        ->sum('coin'),
                ];
            }

            $this->success("OK", [
                'total' => $result['total'],
                'list' => $aRet,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
