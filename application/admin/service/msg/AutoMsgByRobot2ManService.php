<?php
/**
 *自动机器人给男性用户发送营销性消息
 */
namespace app\admin\service\msg;

use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;
use app\admin\service\MaterialService;
use app\admin\service\txyun\YuntongxinService;

class AutoMsgByRobot2ManService extends BaseService
{
    /**
     * 给男用户发送单聊消息
     *
     * 1：找到需要推送消息的男用户 根据配置条件
     * 2：找到符合条件的女主播
     * @throws
     */
    public static function sendMsg()
    {
        // 频率控制
        if (! self::sendFrequenceCrontol()) {
            return false;
        }

        $time = time();
        $page = 1;
        $pageSize = 1000;

        $sendMsgConfig = cmf_get_option('send_msg_2_man_by_robot');
        if (!isset($sendMsgConfig['man_nearest_online']) || $sendMsgConfig['man_nearest_online'] < 0) {
            return false;
        } elseif ($sendMsgConfig['man_nearest_online'] == 0) {
            $filTime = 0;
        } else {
            $filTime = $time - 3600 * $sendMsgConfig['man_nearest_online']; // 用户在 X 小时内在线
        }

        // 判断当前有没有符合推送营销消息条件的用户
        $mansCount = Db::name('user')
            ->alias('u')
            ->join('user_token t', "t.user_id = u.id AND t.last_online_time >= {$filTime}")
            ->where('u.user_type', 2)
            ->where('u.sex', 1)
            ->count();
        if (empty($mansCount)) {
            return false;
        }

        // 随机获取一个符合条件的机器人
        $robotUId = self::randomOneRobot();
        if ($robotUId == false) {
            return false;
        }
        $robotFind = Db::name('user')->field('id,user_type,user_nickname,avatar')->find($robotUId);

        // 获取机器人归属的客服id
        $customId = Db::name('allot_robot')->where('robot_id', $robotUId)->value('custom_id');
        if (! $customId) {
            return false;
        }
        // 随机获取一个消息模版
        $msgTempRow = self::randomOneRowFromTable('t_msg_template', " tmp_code='AUTO_SEND_TO_MAN' ");

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

        while (true) {
            // 分页查找接受消息的男用户
            $manUsers = Db::name('user')
                ->alias('u')
                ->join('user_token t', "t.user_id = u.id AND t.last_online_time > {$filTime}")
                ->where('u.user_type', 2)
                ->where('u.sex', 1)
                ->field("u.id,u.user_nickname,u.avatar")
                ->page($page, $pageSize)
                ->select()
                ->toArray();
            if (empty($manUsers)) {
                break;
            }

            foreach ($manUsers as $manUser) {
                $req_data = [
                    "SyncOtherMachine" => 2,           //消息不同步至发送方
                    "From_Account" => strval($customId), // 发送者 客服id
                    "To_Account" => strval($manUser['id']),        //目标账户列表
                    "MsgRandom" => intval(mt_rand(1,10000) . mt_rand(1,10000)),
                    "MsgBody" => [                     //消息
                        [
                            "MsgType" => "TIMTextElem",  //消息类型，TIMTextElem为文本消息
                            "MsgContent" => [
                                "Text" => $msgTempRow['content']   //消息文本
                            ]
                        ],
                        [
                            "MsgType" => "TIMCustomElem",  //自定义类型
                            "MsgContent" => [
                                "Data" => json_encode([
                                    'userIMId' => strval($customId), // 发送者（云通信）id(客服id)
                                    'userId' => strval($robotFind['id']), // 发送者id
                                    'userName' => $robotFind['user_nickname'], // 发送者昵称
                                    'userAvatar' => MaterialService::getFullUrl($robotFind['avatar']), // 发送者头像
                                    'otherPartyIMId' => strval($manUser['id']), // 接受者（云通信）id
                                    'otherPartyId' => strval($manUser['id']), // 接受者id
                                    'otherPartyName' => $manUser['user_nickname'], // 接受者昵称
                                    'otherPartyAvatar' => MaterialService::getFullUrl($manUser['avatar']), // 接受者头像
                                ])
                            ]
                        ]
                    ]
                ];

                $result = $api->comm_rest('openim', 'sendmsg', $req_data); // 发单聊消息
                if ($result == null || $result['ActionStatus'] != 'OK') {
                    Log::write(sprintf('%s：批量发单聊消息失败：%s', __METHOD__, $result),'error');
                    self::exceptionError('批量发单聊消息失败', -1111);
                    return false;
                }
            }

            $page++;
        }

        return true;
    }

    /**
     * 发送频率控制
     * @return bool
     */
    public static function sendFrequenceCrontol()
    {
        $time = time();

        $fixActivityTime_1 = ['12:29'];
        $fixActivityTime_2 = ['12:29', '19:29'];
        $fixActivityTime_3 = ['12:29', '19:29', '22:41'];

        // 获取配置数据
        $sendMsg_2_manByRobot = cmf_get_option('send_msg_2_man_by_robot');
        $sendFrequence = isset($sendMsg_2_manByRobot['send_frequence']) ? $sendMsg_2_manByRobot['send_frequence'] : 1;

        switch ($sendFrequence) {
            case 1: //每天唤醒1次
                if (in_array(date('H:i', $time), $fixActivityTime_1)) {
                    return true;
                }
                break;
            case 2: //每天唤醒2次
                if (in_array(date('H:i', $time), $fixActivityTime_2)) {
                    return true;
                }
                break;
            case 3: //每天唤醒3次
                if (in_array(date('H:i', $time), $fixActivityTime_3)) {
                    return true;
                }
                break;
            default:
                return false;
        }

        return false;
    }

    /**
     * 获取一个符合机器人数据
     *   随机获取一条机器人记录
     * @return bool
     */
    public static function randomOneRobot()
    {
        $womanUserIds = Db::name('user')->where('sex', 2)->where('user_type', 3)->column('id');

        if (! empty($womanUserIds)) {
            return $womanUserIds[array_rand($womanUserIds)];
        } else {
            return false;
        }
    }

    /**
     * 从一张表中随机获取一条记录
     *
     * @param string $table
     * @param string $where
     * @return bool|array
     */
    public static function randomOneRowFromTable($table, $where = '1')
    {
        $sql = "SELECT * FROM `{$table}` AS t1 
                JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `{$table}` WHERE {$where})-(SELECT MIN(id) FROM `$table` WHERE {$where}))+(SELECT MIN(id) FROM `$table` WHERE {$where})) AS id) AS t2
                WHERE t1.id >= t2.id AND {$where}
                ORDER BY t1.id LIMIT 1";
        $res = Db::query($sql);

        if (!empty($res[0])) {
            return $res[0];
        } else {
            return false;
        }
    }
}