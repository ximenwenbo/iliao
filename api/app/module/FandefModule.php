<?php
/**
 * 范定制功能模块
 */
namespace api\app\module;

use think\Db;
use dctxyun\Common;

class FandefModule extends BaseModule
{
    /**
     * 获取头像完整路径
     * @param $object
     * @param bool $verify
     * @return string
     */
    public static function getAvatarFullUrl($object, $verify = false)
    {
        if (empty($object)) {
            return '';
        }

        // 如果链接头有htpp，就不替换了，第三方登录时用到
        if (preg_match('/^http/', $object)) {
            return $object;
        }

        $trtc_option = cmf_get_option('trtc');

        // 资源需要审核通过才显示
        if ($verify) {
            if (Db::name('oss_material')->where('object', $object)->value('status') != 2) {
                return sprintf('%s%s', $trtc_option['cosCdn'], 'assets/avatar-default.png');
            }
        }

        $ext = strrchr($object,'.');
        if (in_array($ext, ['.bmp', '.jpg', '.jpeg', '.png', '.gif', '.webp'])) {
            return sprintf('%s%s?imageMogr2/thumbnail/600x', $trtc_option['cosCdn'], $object);
        }
        return sprintf('%s%s', $trtc_option['cosCdn'], $object);
    }

    /**
     * 创建推流，并返回推流地址
     * @param $userId
     * @param $streamId
     * @param $aOption
     * @param $streamType
     * @return string
     */
    public static function createPushStream($userId, $streamId, $aOption, $streamType = 1)
    {
        $insert = [
            'user_id' => $userId,
            'option_type' => $aOption['option_type'],
            'option_id' => $aOption['option_id'],
            'option_class_id' => $aOption['option_class_id'],
            'stream_id' => $streamId,
            'stream_type' => $streamType,
            'mix_stream_session_id' => $streamId
        ];

        $streamAutoId = Db::name('live_stream')->insertGetId($insert);

        return Common::getTLiveUrl($streamId, ['stream_auto_id' => $streamAutoId]);
    }
}