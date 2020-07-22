<?php
/**
 * 文件资源功能模块
 */
namespace api\app\module;

use OSS\Core\OssException;
use think\Db;
use think\Log;
use dctxyun\Cosapi;
use think\Exception;
use api\app\module\BaseModule;

class MaterialModule extends BaseModule
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

        $ext = strrchr($object,'.');
        if (in_array($ext, ['.bmp', '.jpg', '.jpeg', '.png', '.gif', '.webp'])) {
            return sprintf('%s%s?imageMogr2/thumbnail/600x', $trtc_option['cosCdn'], $object);
        }
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
     * 批量删除文件
     * @param $aObject
     * @return bool
     */
    public static function delMultipleObject($aObject)
    {
        try {
            $oCosapi = new Cosapi();
            $result = $oCosapi->deleteMultipleObject($aObject);
            if ($result !== true) {
                self::exceptionError('批量删除失败，请检查配置并重新操作');
                return false;
            }

            return true;

        } catch (Exception $e) {
            self::exceptionError($e->getMessage());
            return false;
        }
    }
}