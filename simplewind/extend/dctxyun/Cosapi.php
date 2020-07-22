<?php
namespace dctxyun;

use think\Log;
use think\Exception;

/**
 * 腾讯云--COS类方法
 */
class Cosapi extends Base
{
    public $bucket = null;
    public $cosClient = null;

    public function __construct()
    {
        require EXTEND_PATH . 'cos-php-sdk-v5/vendor/autoload.php';
        $trtc = cmf_get_option('trtc');
        $secretId = $trtc['cosSecretId']; //替换为您的永久密钥 SecretId
        $secretKey = $trtc['cosSecretKey']; //替换为您的永久密钥 SecretKey
        $region = $trtc['cosArea']; //设置一个默认的存储桶地域
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => 'https', //协议头部，默认为 http
                'credentials'=> array(
                    'secretId'  => $secretId ,
                    'secretKey' => $secretKey
                )
            )
        );

        $this->bucket = $trtc['cosBucket'];
        $this->cosClient = $cosClient;
    }

    /**
     * 获取密钥预签名Url
     */
    public function getSignUrl($object)
    {
        $cosClient = $this->cosClient;

        // 简单上传预签名
        try {
            $command = $cosClient->getCommand('putObject', array(
                'Bucket' => $this->bucket, //存储桶，格式：BucketName-APPID
                'Key' => $object, //对象在存储桶中的位置，即对象键
                'Body' => '', //
            ));
            $signedUrl = $command->createPresignedUrl('+10 minutes');

            // 请求成功
            return $signedUrl;

        } catch (\Exception $e) {
            // 请求失败
            Log::write(sprintf('%s：请求腾讯云临时密钥预签名API系统异常：%s', __METHOD__, $e->getMessage()),'error');
        }

        return false;
    }

    /**
     * 上传object
     * @param $content
     * @param $object
     * @return bool|null
     */
    public function uploadObject($content, $object)
    {
        $cosClient = $this->cosClient;

        // 上传文件(文件流)
        try {
            $cosClient->putObject(array(
                'Bucket' => $this->bucket,
                'Key' => $object,
                'Body' => $content
            ));

            return true;

        } catch (\Exception $e) {
            Log::write(sprintf('%s：请求腾讯云上传文件API系统异常：%s', __METHOD__, $e->getMessage()),'error');

            return false;
        }
    }

    /**
     * 删除object
     * @param $content
     * @param $object
     * @return bool|null
     */
    public function deleteObject($object)
    {
        $cosClient = $this->cosClient;

        try {
            $cosClient->deleteObject(array(
                'Bucket' => $this->bucket,
                'Key' => $object,
            ));

            return true;

        } catch (\Exception $e) {
            Log::write(sprintf('%s：请求腾讯云删除文件API系统异常：%s', __METHOD__, $e->getMessage()),'error');

            return false;
        }
    }

    /**
     * 批量删除object
     * @param $content
     * @param $aObject
     * @return bool|null
     */
    public function deleteMultipleObject($aObject)
    {
        $objects = [];
        foreach ($aObject as $val) {
            $objects[] = [
                'Key' => $val,
            ];
        }

        $cosClient = $this->cosClient;

        try {
            $cosClient->deleteObjects(array(
                'Bucket' => $this->bucket,
                'Objects' => $objects,
            ));

            return true;

        } catch (\Exception $e) {
            Log::write(sprintf('%s：请求腾讯云删除文件API系统异常：%s', __METHOD__, $e->getMessage()),'error');

            return false;
        }
    }
}