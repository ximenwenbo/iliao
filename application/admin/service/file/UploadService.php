<?php
namespace app\admin\service\file;

use app\admin\service\BaseService;
use think\Exception;
use think\Log;
use dctxyun\Cosapi;

class UploadService extends BaseService
{
    /**
     * 上传文件
     * @param $content
     * @param $object
     * @return bool
     */
    public static function uploadObject($content, $object)
    {
        // 上传至腾讯云COS
        try {
            $oCosapi = new Cosapi();
            $result = $oCosapi->uploadObject($content, $object);
            if ($result !== true) {
                self::exceptionError('上传失败，请检查配置并重新操作');
                return false;
            }

            return true;

        } catch (Exception $e) {
            self::exceptionError($e->getMessage());
            return false;
        }
    }

    /**
     * 删除文件
     * @param $object
     * @return bool
     */
    public static function delObject($object)
    {
        try {
            $oCosapi = new Cosapi();
            $result = $oCosapi->deleteObject($object);
            if ($result !== true) {
                self::exceptionError('删除失败，请检查配置并重新操作');
                return false;
            }

            return true;

        } catch (Exception $e) {
            self::exceptionError($e->getMessage());
            return false;
        }
    }

}