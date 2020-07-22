<?php
/**
 * 提示框消息任务
 * User: coase
 * Date: 2019/3/21
 */
namespace app\admin\job;

use think\Db;
use think\Log;
use think\Exception;
use think\queue\Job;
use app\admin\service\MaterialService;
use app\admin\service\txyun\YuntongxinService;

class PromptMsgJob
{

    /**
     * 新用户注册任务
     * @param Job $job
     * @param $data
     */
    public function newUserTask(Job $job, $data)
    {
        try {
            //这里执行具体的任务
            $userFind = Db::name('user')->where('id', $data['user_id'])->field('id,user_nickname,avatar,sex,user_type,from_uid')->find();

            # 获取该用户的运营客服，如果不是机器人，则该值就是用户的id
            if ($userFind['user_type'] == 3) {
                $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $userFind['id'])->value('custom_id');
            }
            $prom_custom_uid = isset($prom_custom_uid) ? $prom_custom_uid : $userFind['id'];

            $page = 1;
            $pageSize = 500;
            $aData = [
                'user_id' => $userFind['id'],
                'user_nickname' => $userFind['user_nickname'],
                'avatar' => MaterialService::getFullUrl($userFind['avatar']),
                'sex' => $userFind['sex'],
                'prom_custom_uid' => $prom_custom_uid,
                'content' => '有一个新用户刚刚注册进来',
            ];
            # 获取配置参数
            $trtc = cmf_get_option('trtc');

            // 设置 REST API 调用基本参数
            $identifier = $trtc['identifier'];
            $private_pem_path = ROOT_PATH . $trtc['private_pem'];
            $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
            $expiry_after = 86400*365; // 过期时间一年
            $api = YuntongxinService::initImAPI(); // 初始化API
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature); // 生成签名

            // 如果用户是由推广员，则只给该推广员发新用户弹框消息
            if (! empty($userFind['from_uid'])) {
                $aUserId = [strval($userFind['from_uid'])];

                //发送消息
                //拼装消息体
                $content = ['code' => 'new_user_notice', 'subject' => '新用户消息', 'content' => ['style' => 1, 'content' => $aData]];
                $msg_content = array(); #构造高级接口所需参数
                //创建array 所需元素
                $msg_content_elem = array(
                    'MsgType' => 'TIMCustomElem',       //自定义类型
                    'MsgContent' => array(
                        'Data' => json_encode($content),
                    )
                );
                //将创建的元素$msg_content_elem, 加入array $msg_content
                array_push($msg_content, $msg_content_elem);

                $result = $api->openim_batch_sendmsg2($aUserId, $msg_content);

                if ($result == null || $result['ActionStatus'] != 'OK') {
                    // 签名生成失败
                    Log::write(sprintf('%s：[任务执行异常]新用户弹框消息发送失败：%s，接收者：%s，内容：%s',
                        __METHOD__, var_export($result,true), var_export($aUserId,true), var_export($msg_content,true)
                    ),'error');
                }
            } else {
//                do {
                    // 获取消息接受者(们)
                    $aUserId = $this->getReceiveUser4newUserTask($data, $page, $pageSize);

                    //发送消息
                    //拼装消息体
                    $content = ['code' => 'new_user_notice', 'subject' => '新用户消息', 'content' => ['style' => 1, 'content' => $aData]];
                    $msg_content = array(); #构造高级接口所需参数
                    //创建array 所需元素
                    $msg_content_elem = array(
                        'MsgType' => 'TIMCustomElem',       //自定义类型
                        'MsgContent' => array(
                            'Data' => json_encode($content),
                        )
                    );
                    //将创建的元素$msg_content_elem, 加入array $msg_content
                    array_push($msg_content, $msg_content_elem);

                    $result = $api->openim_batch_sendmsg2($aUserId, $msg_content);

                    if ($result == null || $result['ActionStatus'] != 'OK') {
                        // 签名生成失败
                        Log::write(sprintf('%s：[任务执行异常]群发，新用户弹框消息发送失败：%s，接收者：%s，内容：%s',
                            __METHOD__, var_export($result, true), var_export($aUserId, true), var_export($msg_content, true)
                        ), 'error');
                    }

//                    $page++;
//                } while (!empty($aUserId));
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误，新用户弹框消息发送[任务执行异常]：%s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
        }

        if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
            Log::write(sprintf('%s：任务执行异常，重试3次以上后结束，data：%s', __METHOD__, var_export($data, true)),'error');
            // 也可以重新发布这个任务
            $job->release(3600); //$delay为延迟时间
            exit;
        }


        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
        exit;
    }

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
        Log::write(sprintf('%s：任务执行异常，任务达到最大重试次数后，失败了，data：%s', __METHOD__, var_export($data, true)),'error');

    }

    /**
     * 获取接收消息的用户
     * @param $type
     * @param $page
     * @param $pageSize
     * @return array
     */
    private function getReceiveUser4newUserTask($data, $page, $pageSize)
    {
//        Log::write(sprintf('%s：新用户注册提醒，参数，$type：%s，$page：%s，$pageSize：%s', __METHOD__, $type, $page, $pageSize),'error');
        $userIds = [];
        $type = $data['type'];

        if ($type == 'all_custom') {
            $userIds = Db::name('role_user')->where('role_id', 3)->page($page, $pageSize)->column('user_id');

        } elseif ($type == 'merchant_custom') {
            $merchantId = $data['merchant_id'];
            $userIds = Db::name('merchant_customer')->alias('c')
                ->join('user u', 'u.id=c.user_id AND u.user_type=2')
                ->where('c.status', 1)
                ->where('c.m_id', $merchantId)
                ->column('c.user_id');
        }
//        Log::write(sprintf('%s：新用户注册提醒，获取的客服uid：%s', __METHOD__, var_export($userIds, true)),'error');

        return array_map(function ($uid) {return strval($uid);}, $userIds);
    }

}
