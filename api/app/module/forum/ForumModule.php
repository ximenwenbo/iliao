<?php
/**
 * 社区功能模块
 */
namespace api\app\module\forum;

use function Qiniu\base64_urlSafeEncode;
use think\Db;
use think\Log;
use think\Exception;
use api\app\module\BaseModule;
use api\app\module\MaterialModule;

class ForumModule extends BaseModule
{
    /**
     * 新增动态
     * @author coase
     * @param int $userId
     * @param array $param
     * @return bool
     * @throws Exception
     */
    public static function addDynamic($userId, $param)
    {
        $time = time();

        // 启动事务
        Db::startTrans();
        try {
            # 新增动态
            $addDynamic = [
                'user_id' => $userId,
                'type' => $param['type'],
                'title' => !empty($param['title']) ? $param['title'] : '',
                'content' => !empty($param['content']) ? $param['content'] : '',
                'picture' => !empty($param['picture']) ? $param['picture'] : '',
                'video' => !empty($param['video']) ? $param['video'] : '',
                'province_name' => !empty($param['province_name']) ? $param['province_name'] : '',
                'city_name' => !empty($param['city_name']) ? $param['city_name'] : '',
                'circle_id' => !empty($param['circle_id']) ? $param['circle_id'] : 0,
                'status'  => 2, // 状态（2:正常显示）
                'create_time' => $time,
                'y_level' => 8,
            ];

            $dynamicId = Db::name('forum_dynamic')->insertGetId($addDynamic);

            # 新增操作记录
            $addRecord = [
                'user_id' => $userId,
                'action' => 'NEW',
                'object_id' => $dynamicId,
                'object_type' => 'DYNAMIC',
                'object_user_id' => $userId,
                'create_time' => $time
            ];
            Db::name('forum_opt_record')->insertGetId($addRecord);

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new Exception('系统异常,' . $e->getMessage(), 9901);
        }

        return true;
    }

    /**
     * 新增动态回复，并返回id
     * @author coase
     * @param int $userId
     * @param array $param
     * @return bool
     * @throws Exception
     */
    public static function addReply4DynamicGetId($userId, $param)
    {
        $time = time();

        $dynamicRow = Db::name('forum_dynamic')->find($param['dynamic_id']);
        if (! $dynamicRow) {
            self::exceptionError('动态内容不存在');
            return false;
        }
        if (! empty($param['parent_reply_id'])) {
            $parentReplyCount = Db::name('forum_opt_record')
                ->where(['object_id' => $param['dynamic_id'], 'reply_id' => $param['parent_reply_id']])
                ->count();
            if (! $parentReplyCount) {
                self::exceptionError('回复内容不存在');
                return false;
            }
        } else {
            $currMaxFloor = Db::name('forum_opt_record')
                ->where(['action' => 'REPLY', 'object_id' => $param['dynamic_id'], 'parent_reply_id' => 0])
                ->max('reply_floor');
        }

        // 启动事务
        Db::startTrans();
        try {
            # 新增回复
            $addReply = [
                'user_id' => $userId,
                'content' => $param['content'],
                'reviewed_user_id' => !empty($param['reviewed_user_id']) ? $param['reviewed_user_id'] : '',
                'status'  => 2,
                'create_time' => $time,
            ];
            $replyId = Db::name('forum_reply')->insertGetId($addReply);

            # 新增操作记录
            $addRecord = [
                'user_id' => $userId,
                'action' => 'REPLY',
                'object_id' => $dynamicRow['id'],
                'object_type' => 'DYNAMIC',
                'object_user_id' => $dynamicRow['user_id'],
                'reply_id' => $replyId,
                'parent_reply_id' => !empty($param['parent_reply_id']) ? $param['parent_reply_id'] : 0,
                'reply_floor' => isset($currMaxFloor) ? $currMaxFloor+1 : 0,
                'create_time' => $time
            ];
            Db::name('forum_opt_record')->insertGetId($addRecord);

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new Exception('系统异常,' . $e->getMessage(), 9901);
        }

        return $replyId;
    }

    /**
     * 格式化oss资源输出
     * @author coase
     * @param $dataStr
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function formatOssReturn($dataStr)
    {
        $dataStr = trim($dataStr);
        if (empty($dataStr)) {
            return [];
        }
        $dataArr = explode(',', $dataStr);
        if (empty($dataArr)) {
            return [];
        }

        // 水印配置
        $watermark = cmf_get_option('watermark');
        $imageStatus = $watermark['image_status'];
        $videoStatus = $watermark['video_status'];

        $aRet = [];
        $materialSelect = Db::name('oss_material')->whereIn('object', $dataArr)->select();
        foreach ($materialSelect as $material) {
            $extraInfo = json_decode($material['extra_info'], true);
            if ($material['mime_type'] == 'image') {
                $object = $material['object'];
            } else {
                $object = $material['object'];
            }

            if ($material['mime_type'] == 'video') {
                $cover_img = MaterialModule::getFullUrl($material['video_cover_img']);
            }

            if ($material['mime_type']=='image' && $imageStatus == 1) {
                $suffix = sprintf('?watermark/1/image/%s/gravity/southeast', base64_urlSafeEncode($watermark['image_url']));
            } elseif ($material['mime_type'] == 'video' && $videoStatus == 1) {
                $suffix = $watermark['video_suffix'];
            } else {
                $suffix = '';
            }

            $aRet[] = [
                'object' => $object . $suffix,
//                'mime_type' => $material['mime_type'],
//                'class_id' => $material['class_id'],
                'cover_img' => isset($cover_img) ? $cover_img : '',
                'thumb_img' => $material['mime_type']=='image' ? $material['object'] . $suffix : '',
                'extra_info'=> [
                    'width' => isset($extraInfo['width']) ? intval($extraInfo['width']) : 0,
                    'height' => isset($extraInfo['height']) ? intval($extraInfo['height']) : 0,
                ]
            ];
        }

        return $aRet;
    }

    /**
     * 计算距离当前多久前
     * @param $the_time
     * @return string
     */
    public static function timeTran($the_time)
    {
        $now_time  = time();
        $show_time = $the_time;
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return '刚刚';
        } else {
            if ($dur < 60) {
                return '刚刚';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return date('m-d', $the_time);
                        }
                    }
                }
            }
        }
    }
}