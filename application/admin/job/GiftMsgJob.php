<?php
/**
 * 赠送礼物消息任务
 * User: coase
 * Date: 2019/3/21
 */
namespace app\admin\job;

use function Couchbase\defaultDecoder;
use think\Db;
use think\Log;
use dctool\Fun;
use think\Exception;
use think\queue\Job;
use app\admin\service\txyun\YuntongxinService;

class GiftMsgJob
{

    public function fire(Job $job, $data)
    {
        try {
            //这里执行具体的任务
            $fromUsername = Db::name('user')->where('id', $data['from_uid'])->value('user_nickname');
            $toUsername = Db::name('user')->where('id', $data['to_uid'])->value('user_nickname');
            $giftFind = Db::name('gift')->where('uni_code', $data['gift_uni_code'])->find();
            $imgUrl = $this->getLocalImgUrl($giftFind['effect_img']);

            switch ($giftFind['uni_code']) {
                case 'PAOCHE':
                    $giftPrefix = '一辆';
                    break;
                case 'YOUTING':
                    $giftPrefix = '一艘';
                    break;
                default:
                    $giftPrefix = '一个';
            }

            $page = 1;
            $pageSize = 500;
            $aData = [
                'from_username' => $fromUsername,
                'to_username' => $toUsername,
                'gift_name' => $giftPrefix . $giftFind['name'],
                'img_url' => $imgUrl
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

            do {
                // 获取消息接受者(们)
                $aUserId = $this->getReceiveUser($data['type'], $page, $pageSize);

                //发送消息
                //拼装消息体
                $content = ['code' => 'gift_notice', 'subject' => '礼物弹幕消息', 'content' => ['style' => 1, 'content' => $aData]];
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
                    Log::write(sprintf('%s：[任务执行异常]礼物弹幕消息发送失败：%s', __METHOD__, var_export($result, true)),'error');
                }

                $page++;
            } while(! empty($aUserId));

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误，礼物弹幕消息发送[任务执行异常]：%s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
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
    private function getReceiveUser($type, $page, $pageSize)
    {
        $userIds = [];

        if ($type == 'all_user') {
            $userIds = Db::name('user')->where('user_type', 2)->page($page, $pageSize)->column('id');
        }

        return array_map(function ($uid) {return strval($uid);}, $userIds);
    }

    /**
     * 转化本地服务器保存的图片的文件路径，为可以访问的url
     * @param $file
     * @return string
     */
    private function getLocalImgUrl($file)
    {
        if (strpos($file, "http") === 0) {
            $url = $file;
        } elseif (strpos($file, "https") === 0) {
            $url = $file;
        } elseif (strpos($file, "/") === 0) {
            $url = config('option.admin_domain') . $file;
        } else {
            $url = config('option.admin_domain') . '/' . $file;
        }

        return $url;
    }
}
