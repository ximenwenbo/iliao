<?php
/**
 * 机器人自动进入直播间
 */
namespace app\admin\service\commandir;

use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;
use app\admin\service\MaterialService;
use app\admin\service\txyun\YuntongxinService;

class AutoViewerService extends BaseService
{
    // 云通信api初始化对象
    public static $timRestApi = null;

    /**
     * 机器人自动进入直播间
     * @param array $liveRow
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function sendRobot($liveRow)
    {
        $robotInfo = Db::name('user')
            ->alias('u')
            ->where('user_type', 3)
            ->whereNotIn('id', function ($query){
                $query->name('live_home_viewer')->where('user_type', 3)->field('user_id');
            })
            ->orderRaw('rand()')
            ->field('u.id user_id,u.user_nickname,u.avatar,u.vip_expire_time')
            ->find();

        if (! $robotInfo) {
            Log::write(sprintf('%s：自动进入直播间脚本失败，机器人数量不足', __METHOD__),'error');
            self::exceptionError('自动进入直播间脚本失败', -1111);
            return false;
        }

        $api = self::initImAPI();

        // 发群消息
        $groupMsgData = [
            "GroupId" => strval($liveRow['user_id']), // 要操作的群组
            "Content" => json_encode(
                [
                    'cmd' => 'RobotInGroupMsg', // 模拟机器人进入群消息
                    'data' => [
                        'cmd' => '2',  // 2加入直播 3退出直播
                        'identityType' => '0', // 身份 0普通 1主播
                        'isGuard' => '0', // 是否守护 0否 1是
                        'isVip' => (string) \api\app\module\VipModule::checkIsVip($robotInfo['vip_expire_time']), // 是否vip 0否 1是
                        'msg' => '',
                        'userId' => strval($robotInfo['user_id']),
                        'userAvatar' => MaterialService::getFullUrl($robotInfo['avatar']), // 发送者头像
                        'userName' => $robotInfo['user_nickname'], // 发送者昵称
                        'user_level' => (string) \api\app\module\UserModule::getUserLevelByUid($robotInfo['user_id']), // 用户财富等级
                    ]
                ]
            )
        ];

        $result = $api->comm_rest('group_open_http_svc', 'send_group_system_notification', $groupMsgData); // 发群通知
        if ($result == null || $result['ActionStatus'] != 'OK') {
            Log::write(sprintf('%s：发群聊消息失败：%s', __METHOD__, var_export($result, true)),'error');
            self::exceptionError('发群聊消息失败', -1111);
            return false;
        }

        Db::name('live_home_viewer')->insert([
            'user_id' => $robotInfo['user_id'],
            'live_id' => $liveRow['id'],
            'live_user_id' => $liveRow['user_id'],
            'user_type' => 3,
            'status' => 1
        ]);

        Db::name('live_home')->where('id', $liveRow['id'])->update([
            'online_viewer' => Db::raw('online_viewer+1'),
            'total_viewer' => Db::raw('total_viewer+1'),
        ]);

        return true;
    }

    /**
     * 机器人自动退出直播间
     * @param array $liveRow
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function sendRobotOut($liveRow)
    {
        $api = self::initImAPI();

        $robotViewerFind = Db::name('live_home_viewer')
            ->where(['live_id' => $liveRow['id'], 'user_type' => 3, 'status' => 1])
            ->orderRaw('rand()')
            ->find();
        if (! $robotViewerFind) {
            return true;
        }

        $robotInfo = Db::name('user')->find($robotViewerFind['user_id']);
        if (! $robotInfo) {
            return true;
        }

        // 发群消息
        $groupMsgData = [
            "GroupId" => strval($liveRow['user_id']), // 要操作的群组
            "Content" => json_encode(
                [
                    'cmd' => 'RobotInGroupMsg', // 模拟机器人进入群消息
                    'data' => [
                        'cmd' => '3',  // 2加入直播 3退出直播
                        'identityType' => '0', // 身份 0普通 1主播
                        'isGuard' => '0', // 是否守护 0否 1是
                        'isVip' => (string) \api\app\module\VipModule::checkIsVip($robotInfo['vip_expire_time']), // 是否vip 0否 1是
                        'msg' => '',
                        'userId' => strval($robotInfo['id']),
                        'userAvatar' => MaterialService::getFullUrl($robotInfo['avatar']), // 发送者头像
                        'userName' => $robotInfo['user_nickname'], // 发送者昵称
                        'user_level' => (string) \api\app\module\UserModule::getUserLevelByUid($robotInfo['id']), // 用户财富等级
                    ]
                ]
            )
        ];

        $result = $api->comm_rest('group_open_http_svc', 'send_group_system_notification', $groupMsgData); // 发群通知
        if ($result == null || $result['ActionStatus'] != 'OK') {
            Log::write(sprintf('%s：发群聊消息失败：%s', __METHOD__, var_export($result, true)),'error');
            self::exceptionError('发群聊消息失败', -1111);
            return false;
        }

        Db::name('live_home_viewer')->where('id', $robotViewerFind['id'])->delete();

        Db::name('live_home')->where('id', $liveRow['id'])->setDec('online_viewer', 1);;

        return true;
    }

    /**
     * 初始化云通信API
     *
     * @return \RestAPI
     * @throws Exception
     */
    public static function initImAPI()
    {
        if (self::$timRestApi) {
            return self::$timRestApi;
        }

        # 获取配置参数
        $trtc = cmf_get_option('trtc');
        // 设置 REST API 调用基本参数
        $identifier = $trtc['identifier'];
        $private_pem_path = ROOT_PATH . $trtc['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        // 初始化API
        $api = YuntongxinService::initImAPI();
        // 生成签名
        $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);

        self::$timRestApi = $api;
        return $api;
    }
}