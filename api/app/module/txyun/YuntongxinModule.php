<?php
/**
 * 云通信功能模块
 * Class YuntongxinModule
 * @package api\app\module
 */
namespace api\app\module\txyun;
use think\Db;
use think\Log;
use think\Exception;
use api\app\module\BaseModule;

class YuntongxinModule extends BaseModule
{
    // 云通信api初始化对象
    public static $timRestApi = null;

    /**
     * 初始化用户在腾讯云的信息（新注册用户必须调用该方法）
     *
     * @param int $userId 用户id
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function initYuntongxinUser($userId)
    {
        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $yuntongxin['sdkappid'];
        $identifier = $userId;
        $private_pem_path = ROOT_PATH . $yuntongxin['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);

            // 生成签名
            $retSig = $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
            if ($retSig == null) {
                // 签名生成失败
                Log::write(sprintf('%s：生成腾讯云通信用户签名sig失败：%s', __METHOD__, $retSig),'error');
                self::exceptionError('生成腾讯云通信用户签名sig失败', -1011);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：生成腾讯云通信用户签名sig系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('生成腾讯云通信用户签名sig系统异常:' . $e->getMessage());
        }

        # 更新腾讯云通信用户签名sig
        $update = [
            'user_id' => $userId,
            'yuntongxin_user_sig' => $retSig[0],
            'update_time' => time()
        ];
        if (Db::name('user_yuntongxin')->where('user_id', $userId)->count()) {
            Db::name('user_yuntongxin')->where('user_id', $userId)->update($update);
        } else {
            Db::name('user_yuntongxin')->insert($update);
        }

        return $retSig[0];
    }


    /**
     * 初始化用户在腾讯实时音视频的信息（新注册用户必须调用该方法）
     *
     * @param int $userId 用户id
     * @return bool
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function initTRTCUser($userId)
    {
        require EXTEND_PATH . 'txyunusersigsdk/WebRTCSigApi.php';

        # 获取配置参数
        $trtc = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $trtc['sdkappid'];
        $private_pem = file_get_contents(ROOT_PATH . $trtc['private_pem']);
        $public_pem = file_get_contents(ROOT_PATH . $trtc['public_pem']);
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $WebRTCSigApi = new \WebRTCSigApi();
            $WebRTCSigApi->setSdkAppid($sdkappid);
            $WebRTCSigApi->setPrivateKey($private_pem);
            $WebRTCSigApi->setPublicKey($public_pem);

            // 生成签名
            $retSig = $WebRTCSigApi->genUserSig($userId, $expiry_after);
            if ($retSig == null) {
                // 签名生成失败
                Log::write(sprintf('%s：生成腾讯音视频用户签名UserSig失败：%s', __METHOD__, $retSig),'error');
                self::exceptionError('生成腾讯音视频用户签名UserSig失败', -1011);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：生成腾讯音视频用户签名UserSig系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('生成腾讯音视频用户签名UserSig系统异常:' . $e->getMessage());
        }

        # 更新腾讯音视频用户签名
        $update = [
            'user_id' => $userId,
            'trtc_user_sig' => $retSig,
            'update_time' => time()
        ];
        if (Db::name('user_yuntongxin')->where('user_id', $userId)->count()) {
            Db::name('user_yuntongxin')->where('user_id', $userId)->update($update);
        } else {
            Db::name('user_yuntongxin')->insert($update);
        }

        return $retSig;
    }

    /**
     * 签名校验
     * @param $aInput
     * @return bool
     */
    public static function verifyApiSign($aInput)
    {
        $trtc = cmf_get_option('trtc');
        $apiKey = $trtc['api_key']; // 云直播->回调设置->回调密钥 设置成 API鉴权key
        if (empty($aInput['t']) || empty($aInput['sign'])) {
            return false;
        }

        if (md5($apiKey . $aInput['t']) == $aInput['sign']) {
            return true;
        } else {
            return false;
        }
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

        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        // 获取配置参数
        $trtc = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $trtc['sdkappid'];
        $identifier = $trtc['identifier'];

        try {
            // 初始化
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：初始化云通信API系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('初始化云通信API系统异常:' . $e->getMessage());
        }

        self::$timRestApi = $api;
        return self::$timRestApi;
    }

    /**
     * 给某个用户发送系统通知消息
     *
     * @param string|array $toUserId 可以是单个，可以是多个，多个时用数组
     * @param string $tmpContent 模版code 或者是 消息内容
     * @param $toUserId
     * @param $tmpContent
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function pushSysNotice($toUserId, $tmpContent)
    {
        # 获取配置参数
        $trtc = cmf_get_option('trtc');

        # 获取消息内容
        $tmpRow = Db::name('msg_template')->where('tmp_code', $tmpContent)->find();
        if (empty($tmpRow)) {
            $text_content = $tmpContent;
        } else {
            $text_content = $tmpRow['content'];
        }

        // 设置 REST API 调用基本参数
        $identifier = $trtc['identifier'];
        $private_pem_path = ROOT_PATH . $trtc['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        if (is_array($toUserId)) {
            $receiver = array_map(function ($v) {
                return strval($v);
            }, $toUserId);
        } else {
            $receiver = [strval($toUserId)];
        }

        // 初始化API
        $api = self::initImAPI();
        try {
            // 生成签名
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);

            //发送消息
            //拼装消息体
            $content = ['code' => 'system_notice', 'subject' => '系统消息', 'content' => $text_content];
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

            $result = $api->openim_batch_sendmsg2($receiver, $msg_content);

            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：给用户发送系统消息失败：%s，消息体：%s', __METHOD__, var_export($result, true), var_export([$receiver, $msg_content], true)),'error');
                self::exceptionError('给用户发送系统消息失败', -1011);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：给用户发送系统消息系统异常：%s', __METHOD__, $e->getMessage()),'error');
            self::exceptionError('给用户发送系统消息系统异常:' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 给某个用户发送社区消息，点赞or回复
     *
     * @param $toUserId
     * @param string $msgType 类型（like，reply）
     * @return bool
     * @throws Exception
     */
    public static function pushForumMsg($toUserId, $msgType)
    {
        # 获取配置参数
        $trtc = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $identifier = $trtc['identifier'];
        $private_pem_path = ROOT_PATH . $trtc['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        $receiver = [strval($toUserId)];

        // 初始化API
        $api = self::initImAPI();
        try {
            // 生成签名
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);

            //发送消息
            //拼装消息体
            $content = ['code' => 'forum_notice', 'subject' => '社区消息', 'content' => ['type' => $msgType, 'method' => 'plus']]; // method:plus/minus
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

            $result = $api->openim_batch_sendmsg2($receiver, $msg_content);

            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：给用户发送系统消息失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('给用户发送系统消息失败', -1011);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：给用户发送系统消息系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('给用户发送系统消息系统异常:' . $e->getMessage());
        }

        return true;
    }
}