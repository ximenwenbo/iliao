<?php
namespace dctxyun;

use think\Log;
use think\Exception;

/**
 * 腾讯云--IM类方法
 */
class Imapi extends Base
{
    // 云通信api初始化对象
    public static $timRestApi = null;

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
     * 初始化云通信API并且生成管理员用户签名
     *
     * @return \RestAPI
     * @throws Exception
     */
    public static function initImGenerateMasterUserSig()
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
        $private_pem_path = ROOT_PATH . $trtc['private_pem'];
        $signature = EXTEND_PATH . "txyunimsdk/signature/linux-signature64";
        $expiry_after = 86400*365; // 过期时间一年

        try {
            // 初始化
            $api = createRestAPI();
            $api->init($sdkappid, $identifier);

            // 生成签名
            $api->generate_user_sig($identifier, $expiry_after, $private_pem_path, $signature);
        } catch (\RangeException $e) {
            Log::write(sprintf('%s：初始化云通信API系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('初始化云通信API系统异常:' . $e->getMessage());
        }

        self::$timRestApi = $api;
        return self::$timRestApi;
    }

    /**
     * 创建群
     *
     * @param $group_type
     * @param $group_name
     * @param null $owner_id
     * @return bool
     * @throws Exception
     */
    public static function createGroup($group_type, $group_name, $owner_id = null)
    {
        // 初始化
        $api = self::initImGenerateMasterUserSig();

        try {
            $result = $api->group_create_group($group_type, $group_name, $owner_id);

            if ($result == null || $result['ActionStatus'] != 'OK' || empty($result['GroupId'])) {
                // 签名生成失败
                Log::write(sprintf('%s：创建云通信群失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('创建云通信群失败', -1011);
                return false;
            }

            return $result['GroupId'];
        } catch (Exception $e) {
            Log::write(sprintf('%s：创建云通信群系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('创建云通信群系统异常:' . $e->getMessage());
        }
    }

    /**
     * 销毁群
     *
     * @param $group_id
     * @return bool
     * @throws Exception
     */
    public static function destroyGroup($group_id)
    {
        // 初始化
        $api = self::initImGenerateMasterUserSig();

        try {
            $result = $api->group_destroy_group(strval($group_id));

            if ($result == null || $result['ActionStatus'] != 'OK') {
                if ($result['ErrorCode'] == 10010) {
                    return true;
                }
                // 签名生成失败
                Log::write(sprintf('%s：销毁云通信群失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('销毁云通信群失败', -1012);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::write(sprintf('%s：销毁云通信群系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('销毁云通信群系统异常:' . $e->getMessage());
        }
    }
}