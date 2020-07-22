<?php
/**
 * 阿里云OSS功能模块
 */
namespace app\admin\service\aliyun;

use OSS\Core\OssException;
use \think\Log;
use api\app\module\BaseModule;

class AliyunOssModule extends BaseModule
{
    /**
     * 阿里云OSS回调签名认证
     *
     * @return bool
     */
    public static function ossCallbackAuthorization()
    {
        // 1.获取OSS的签名header和公钥url header
        $authorizationBase64 = "";
        $pubKeyUrlBase64 = "";
        /*
         * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
         * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
            RewriteEngine On
            RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
         * */
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL'])) {
            $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
        }

        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '') {
            header("http/1.1 403 Forbidden");
            exit();
        }

        // 2.获取OSS的签名
        $authorization = base64_decode($authorizationBase64);

        // 3.获取公钥
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $pubKey = curl_exec($ch);
        if ($pubKey == "") {
            Log::write(sprintf('%s，阿里云OSS回调，签名验证，未获取到公钥：%s', __METHOD__, var_export($pubKeyUrl,true)),'error');

            header("http/1.1 403 Forbidden");
            exit();
        }

        // 4.获取回调body
        $body = file_get_contents('php://input');

        // 5.拼接待签名字符串
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');
        if ($pos === false) {
            $authStr = urldecode($path)."\n".$body;
        } else {
            $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
        }

        // 6.验证签名
        $ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
        if ($ok !== 1) {
            Log::write(sprintf('%s，阿里云OSS回调，签名验证失败', __METHOD__),'error');

            header("http/1.1 403 Forbidden");
            exit();
        }

        return true;
    }

    /**
     * 获取完整的访问url
     * @param $object
     * @param null $bucket
     * @param null $endpoint
     * @param null $schema
     * @return string
     */
    public static function getFullUrl($object, $bucket = null, $endpoint = null, $schema = null)
    {
        if (empty($object)) {
            return '';
        }

        // 如果链接头有htpp，就不替换了
        if (preg_match('/^http/', $object)) {
            return $object;
        }

        $aliyunOss = cmf_get_option('aliyun_oss');

        if (! $bucket) {
            $bucket = $aliyunOss['bucket'];
        }
        if (! $endpoint) {
            $endpoint = $aliyunOss['endpoint'];
        }
        if (! $schema) {
            $schema = 'http://';
        }

        return sprintf('%s%s.%s/%s', $schema, $bucket, $endpoint, $object);
    }

    /**
     * 获取视频封面图的访问url
     * @param $fullUrl
     * @return string
     */
    public static function getVideoCoverimg($fullUrl)
    {
        return sprintf('%s?%s', $fullUrl, 'x-oss-process=video/snapshot,t_100,f_jpg,w_0,h_0,m_fast');
    }

    /**
     * 上传资源到阿里云OSS
     * @param $content
     * @param $object
     * @return bool|null
     */
    public static function uploadObject($content, $object)
    {
        # 获取配置参数
        $aliyunConfig = cmf_get_option('aliyun_oss');

        $accessKeyId = $aliyunConfig['accessKeyId'];
        $accessKeySecret = $aliyunConfig['accessKeySecret'];
        $endpoint = $aliyunConfig['endpoint'];
        $bucket = $aliyunConfig['bucket'];
        require_once EXTEND_PATH . 'aliyun-oss/autoload.php';

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            return $ossClient->putObject($bucket, $object, $content);
        } catch (OssException $e) {
            self::exceptionError($e->getMessage());
            return false;
        }
    }

    /**
     * 删除阿里云OSS资源
     * @param $objects
     * @return bool|\OSS\Http\ResponseCore
     */
    public static function delObjects($objects)
    {
        # 获取配置参数
        $aliyunConfig = cmf_get_option('aliyun_oss');

        $accessKeyId = $aliyunConfig['accessKeyId'];
        $accessKeySecret = $aliyunConfig['accessKeySecret'];
        $endpoint = $aliyunConfig['endpoint'];
        $bucket = $aliyunConfig['bucket'];
        require_once EXTEND_PATH . 'aliyun-oss/autoload.php';

        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $res = $ossClient->deleteObjects($bucket, $objects);

            return $res;
        } catch (OssException $e) {
            self::exceptionError($e->getMessage());
            return false;
        }
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
        if (!in_array($code, ['trtc_notice', 'trtc_close', 'live_notice', 'live_close'])) {
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
}