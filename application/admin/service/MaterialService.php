<?php
/**
 * 文件资源功能模块
 */
namespace app\admin\service;

use think\Db;
use think\Log;
use think\Exception;

class MaterialService extends BaseService
{
    /**
     * 获取完整的访问url
     * @param $object
     * @return string
     */
    public static function getFullUrl($object)
    {
        if (empty($object)) {
            return '';
        }

        // 如果链接头有htpp，就不替换了，第三方登录时用到
        if (preg_match('/^http/', $object)) {
            return $object;
        }

        $trtc_option = cmf_get_option('trtc');

        return sprintf('%s%s', $trtc_option['cosCdn'], $object);
    }

    /**
     * 给图片加效果
     * @param $fullUrl
     * @param string $effect 例如：blur,r_3,s_2
     * @return string
     */
    public static function addEffect4Img($fullUrl, $effect)
    {return false;
        return sprintf('%s?x-oss-process=image/%s', $fullUrl, $effect);
    }

    /**
     * 获取视频封面图的访问url
     * @param $fullUrl
     * @return string
     */
    public static function getVideoCoverimg($fullUrl)
    {return false;
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