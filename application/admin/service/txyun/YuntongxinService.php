<?php
/**
 * 云通信功能模块
 * Class YuntongxinService
 * @package app\admin\service\txyun
 */
namespace app\admin\service\txyun;
use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;

class YuntongxinService extends BaseService
{
    /**
     * 初始化云通信API
     *
     * @return \RestAPI
     * @throws Exception
     */
    public static function initImAPI()
    {
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

        return $api;
    }

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
        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

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
                Log::write(sprintf('%s：生成腾讯云通信用户签名sig失败：%s', __METHOD__, var_export($retSig, true)),'error');
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
            'trtc_user_sig' => $retSig[0],
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
     * 给一个视频聊天房间中的用户发消息指令
     *
     * @param array $aToUserId 用户id,必须是转为字符串
     * @param int $homeId 房间号
     * @param string $code 该消息的标识 notice:客户端弹框提示 close:客户端关闭聊天
     * @param string $msg 弹框提示消息内容
     * @return bool
     * @throws Exception
     */
    public static function pushMsg4OnLive($aToUserId, $homeId, $code, $msg = '')
    {
        if (!in_array($code, ['notice', 'close'])) {
            self::exceptionError('第二个参数有误', -1099);
            return false;
        }

        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        # 获取配置参数
        $trtc = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $trtc['sdkappid'];
        $identifier = $trtc['identifier'];
        $private_pem_path = ROOT_PATH . $trtc['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);

            // 生成签名
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);

            //发送消息
            //拼装消息体
            $content = ['code' => $code, 'home_id' => $homeId, 'content' => $msg];
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

            $result = $api->openim_batch_sendmsg2($aToUserId, $msg_content);

            if (empty($result['ActionStatus']) || $result['ActionStatus'] !== 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：给视频中的用户发消息失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('给视频中的用户发消息失败', -1021);
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：给视频中的用户发消息系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('给视频中的用户发消息系统异常:' . $e->getMessage());
        }

        return true;
    }

    /**
     * 给某个用户发送系统通知消息
     *
     * @param $toUserId
     * @param string $tmpCode 模版code 或者是 消息内容
     * @return bool
     * @throws Exception
     */
    public static function pushSysNotice($toUserId, $tmpCode)
    {
        # 获取配置参数
        $trtc = cmf_get_option('trtc');

        # 获取消息内容
        $tmpRow = Db::name('msg_template')->where('tmp_code', $tmpCode)->find();
        if (empty($tmpRow)) {
//            Log::write(sprintf('%s：模版CODE不存在：%s', __METHOD__, $tmpCode),'error');
//            self::exceptionError('模版CODE不存在', -1001);
//            return false;
            $text_content = $tmpCode;
        } else {
            $text_content = $tmpRow['content'];
        }

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

    /**
     * 查询敏感词列表
     * @param $word string 敏感词
     * @return bool
     * @throws Exception
     */
    public static function SensitiveWordsGet()
    {
        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';
        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $yuntongxin['sdkappid'];
        $identifier = $yuntongxin['identifier'];
        $private_pem_path = ROOT_PATH . $yuntongxin['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
            $req_data = [];
            $result = $api->comm_rest('openim_dirty_words', 'get', $req_data); // 查询脏字
            var_dump($result);die;
            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：查询敏感词失败：%s', __METHOD__, var_export($result,true)),'error');
                self::exceptionError('查询敏感词失败', -1011);
                return false;
            }
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：查询敏感词系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('查询敏感词系统异常:' . $e->getMessage());
        }

        return true;
    }

    /**
     * 添加敏感词
     * @param $word string 敏感词
     * @return bool
     * @throws Exception
     */
    public static function SensitiveWordsAdd($word)
    {

        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $yuntongxin['sdkappid'];
        $identifier = $yuntongxin['identifier'];
        $private_pem_path = ROOT_PATH . $yuntongxin['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
            $req_data = [
                'DirtyWordsList' => [$word]
            ];
            $result = $api->comm_rest('openim_dirty_words', 'add', $req_data); // 添加脏字
            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：添加敏感词失败：%s', __METHOD__, var_export($result,true)),'error');
                self::exceptionError('添加敏感词失败', -1011);
                return false;
            }
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：添加敏感词系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('添加敏感词系统异常:' . $e->getMessage());
        }

        return true;
    }

    /**
     * 修改敏感词
     * @param $old_word string 需删除的敏感词
     * @param $word string 需添加的敏感词
     * @return bool
     * @throws Exception
     */
    public static function SensitiveWordsUpdate($old_word, $word)
    {

        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $yuntongxin['sdkappid'];
        $identifier = $yuntongxin['identifier'];
        $private_pem_path = ROOT_PATH . $yuntongxin['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);

            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
            $req_data = [
                'DirtyWordsList' => [$old_word]
            ];
            $result = $api->comm_rest('openim_dirty_words', 'delete', $req_data); // 删除脏字
            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：删除敏感词失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('删除敏感词失败', -1011);
                return false;
            }

            $req_data = [
                'DirtyWordsList' => [$word]
            ];
            $result = $api->comm_rest('openim_dirty_words', 'add', $req_data); // 添加脏字
            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：添加敏感词失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('添加敏感词失败', -1011);
                return false;
            }
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：删除敏感词系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('删除敏感词系统异常:' . $e->getMessage());
        }

        return true;
    }

    /**
     * 删除敏感词
     * @param $word string 敏感词
     * @return bool
     * @throws Exception
     */
    public static function SensitiveWordsDel($word)
    {
        require EXTEND_PATH . 'txyunimsdk/TimRestApi.php';

        # 获取配置参数
        $yuntongxin = cmf_get_option('trtc');

        // 设置 REST API 调用基本参数
        $sdkappid = $yuntongxin['sdkappid'];
        $identifier = $yuntongxin['identifier'];
        $private_pem_path = ROOT_PATH . $yuntongxin['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化API
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);

            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
            $req_data = [
                'DirtyWordsList' => [$word]
            ];
            $result = $api->comm_rest('openim_dirty_words', 'delete', $req_data); // 删除脏字
            if ($result == null || $result['ActionStatus'] != 'OK') {
                // 签名生成失败
                Log::write(sprintf('%s：删除敏感词失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('删除敏感词失败', -1011);
                return false;
            }
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：删除敏感词系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('删除敏感词系统异常:' . $e->getMessage());
        }

        return true;
    }
}