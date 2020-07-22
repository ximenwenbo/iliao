<?php
/**
 * 阿里云OSS功能模块
 */
namespace api\app\module\aliyun;

use OSS\Core\OssException;
use think\Db;
use think\Exception;
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

        $aliyunSettings = cmf_get_option('aliyun_oss');

        if (! $bucket) {
            $bucket = $aliyunSettings['bucket'];
        }
        if (! $endpoint) {
            $endpoint = $aliyunSettings['endpoint'];
        }
        if (! $schema) {
            $schema = 'http://';
        }

        return sprintf('%s%s.%s/%s', $schema, $bucket, $endpoint, $object);
    }

    /**
     * 给图片加效果
     * @param $fullUrl
     * @param string $effect 例如：blur,r_3,s_2
     * @return string
     */
    public static function addEffect4Img($fullUrl, $effect)
    {
        return sprintf('%s?x-oss-process=image/%s', $fullUrl, $effect);
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
}