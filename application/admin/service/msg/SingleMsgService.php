<?php
/**
 * 单聊消息类
 */
namespace app\admin\service\msg;

use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;
use app\admin\service\MaterialService;
use app\admin\service\txyun\YuntongxinService;

class SingleMsgService extends BaseService
{
    /**
     * 给男用户发送单聊消息
     *
     * 1：找到需要推送消息的男用户 根据配置条件
     * 2：找到符合条件的女主播
     * @throws
     */
    public static function sendSingleMsg2Man()
    {
        $time = time();

        if (! self::sendFrequenceCrontol()) {
            return false; // 频率控制
        }

        // 随机获取一个符合条件的女主播
        $womenUserId = self::randomOneOnlineWomen();
        if ($womenUserId == false) {
            return false;
        }
        $womenInfo = Db::name('user')->field('id,user_type,user_nickname,avatar')->find($womenUserId);

        // 随机获取一个消息模版
        $msgTempRow = self::randomOneRowFromTable('t_msg_template', " tmp_code='AUTO_SEND_TO_MAN' ");

        $page = 1;
        $pageSize = 1000;

        $sendMsgConfig = cmf_get_option('send_msg_2_man_by_woman');
        if (!isset($sendMsgConfig['man_nearest_online']) || $sendMsgConfig['man_nearest_online'] < 0) {
            return false;
        } elseif ($sendMsgConfig['man_nearest_online'] == 0) {
            $filTime = 0; // 全部
        } else {
            $filTime = $time - 3600 * $sendMsgConfig['man_nearest_online']; // 用户在 X 小时内在线
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

        while (true) {
            // 分页查找接受消息的男用户
            $manUsers = Db::name('user')
                ->alias('u')
                ->join('user_token t', "t.user_id = u.id AND t.last_online_time >= {$filTime}", 'LEFT')
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
                    "From_Account" => strval($womenUserId), // 发送者 客服id
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
                                    'userIMId' => strval($womenUserId), // 发送者（云通信）id(客服id)
                                    'userId' => strval($womenUserId), // 发送者id
                                    'userName' => $womenInfo['user_nickname'], // 发送者昵称
                                    'userAvatar' => MaterialService::getFullUrl($womenInfo['avatar']), // 发送者头像
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

        $fixActivityTime_1 = ['14:29'];
        $fixActivityTime_2 = ['14:29', '22:29'];
        $fixActivityTime_3 = ['14:29', '22:29', '23:41'];

        // 获取配置数据
        $sendSingleMsg2ManSettings = cmf_get_option('send_single_msg_2_man_config');
        $sendFrequence = isset($sendSingleMsg2ManSettings['send_frequence']) ? $sendSingleMsg2ManSettings['send_frequence'] : 1;

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
     * 从一张表中随机获取一条记录
     *
     * @param string $table
     * @param string $where
     * @return bool|array
     * @throws
     */
    public static function randomOneOnlineWomen()
    {
        $time = time();

        // 获取配置数据
        $sendMsgConfig = cmf_get_option('send_msg_2_man_by_woman');
        $womenUserOnline = isset($sendMsgConfig['woman_nearest_online']) ? $sendMsgConfig['woman_nearest_online'] : 0;
        switch ($womenUserOnline) {
            case 0:
                $onlineTime = $time - 600; // 当前在线
                break;
            case 1:
                $onlineTime = $time - 3600; // 近1小时在线
                break;
            case 6:
                $onlineTime = $time - 3600*6; // 近6小时在线
                break;
            case 12:
                $onlineTime = $time - 3600*12; // 近12小时在线
                break;
            case 24:
                $onlineTime = $time - 3600*24; // 近1天在线
                break;
            default:
                $onlineTime = 0; // 全部
        }

        $womanUserIds = Db::name('user')
            ->alias('u')
            ->join('user_token t', 't.user_id = u.id')
            ->where('u.sex', 2)
            ->where('u.user_type', 2)
            ->where('t.last_online_time','>=', $onlineTime)
            ->column('u.id');

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