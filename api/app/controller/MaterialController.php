<?php
/**
 * User: coase
 * Date: 2018-11-01
 * Time: 11:18
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\MaterialModule;
use api\app\module\aliyun\AliyunOssModule;

/**
 * #####物料资源的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.阿里云OSS 回调
 * 2.阿里云OSS 删除object
 * ``````````````````
 */
class MaterialController extends RestBaseController
{
    /**
     * 阿里云OSS 回调
     */
    public function aliyunossCallback()
    {
        try {
            $body = file_get_contents('php://input');

            // 调用参数日志记录
            Log::write(sprintf('%s，阿里云OSS回调，接收的数据：%s', __METHOD__, var_export($body,true)),'log');

            // 签名验证
            AliyunOssModule::ossCallbackAuthorization();

            // 获取body参数
            parse_str($body, $aRequest);

            # 参数校验
            $validate = new Validate([
                'user_id' => 'require',
                'class_id'=> 'require', // 作用类别（1:个人相册 2:个人视频 3:个人语音介绍 4:达人认证 5:实名认证）
                'bucket' => 'require',
                'object' => 'require',
                //            'etag' => 'require',
                'size' => 'require', // 文件大小
                'mime_type' => 'require|in:image,video,audio', // 类型 image,video,audio
                'video_cover_img' => 'requireIf:mime_type,video',
//                'extra_info' => 'require',
                //            'image_info' => 'require',
                //            'video_info' => 'require',
                //            'audio_info' => 'require',
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
                'class_id.require'=> '请输入class_id!',
                'bucket.require' => '请输入bucket!',
                'object.require' => '请输入object!'
            ]);

            if (!$validate->check($aRequest)) {
                // 调用参数日志记录
                Log::write(sprintf('%s，阿里云OSS回调，参数错误：%s', __METHOD__, $validate->getError()),'error');
                $this->error($validate->getError());
            }

            # 检测资源是否存在
            $materialCount = Db::name('oss_material')->where('object', $aRequest['object'])->count();
            if ($materialCount) {
                Log::write(sprintf('%s，阿里云OSS回调，该资源已经存在，object：%s ', __METHOD__, $aRequest['object']),'error');
                $this->error('该资源已经存在');
            }

            # 新增OSS资源
            $input = [
                'user_id' => $aRequest['user_id'],
                'class_id' => $aRequest['class_id'],
                'bucket' => $aRequest['bucket'],
                'object' => $aRequest['object'],
                'etag' => $aRequest['etag'],
                'size' => $aRequest['size'],
                'mime_type' => $aRequest['mime_type'],
                'extra_info' => !empty($aRequest['extra_info']) ? $aRequest['extra_info'] : '',
                'video_cover_img' => !empty($aRequest['video_cover_img']) ? $aRequest['video_cover_img'] : '',
                'image_info' => !empty($aRequest['image_info']) ? $aRequest['image_info'] : '',
                'video_info' => !empty($aRequest['video_info']) ? $aRequest['video_info'] : '',
                'audio_info' => !empty($aRequest['audio_info']) ? $aRequest['audio_info'] : '',
                'create_time' => time()
            ];

            $addId = Db::name('oss_material')->insertGetId($input);

            if ($addId) {
                // 成功
                $materialFind = Db::name('oss_material')->find($addId);

                $fullUrl = MaterialModule::getFullUrl($materialFind['object']);
                $aRet = [
                    'id' => $materialFind['id'],
                    'user_id' => $materialFind['user_id'],
                    'full_url' => $fullUrl,
                    'bucket' => $materialFind['bucket'],
                    'object' => $materialFind['object'],
                ];
                // 为视频生成封面图访问url
                if ($materialFind['mime_type'] == 'video') {
                    $aRet['cover_img'] = MaterialModule::getFullUrl($materialFind['video_cover_img']);
                }

                $this->success('OK', $aRet);
            } else {
                // 失败
                $this->error('失败');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 阿里云OSS 删除object
     */
    public function aliyunossDelObjects()
    {
        if (! $this->getUserId()) {
            die;
        }

        try {
            $validate = new Validate([
                'objects' => 'require', // 多个用英文逗号分割
                'class_id' => 'require|integer'
            ]);

            $validate->message([
                'object.require' => '请输入要删除的object!',
                'class_id.require'  => '请输入class_id！',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $aObject = explode(',', $param['objects']);
            foreach ($aObject as $i=>$item) {
                if (empty($item))
                    unset($aObject[$i]);
            }

            $userId = $this->getUserId();

            // 查询是否存在
            $materialCount = Db::name('oss_material')
                ->where(['user_id'=>$userId, 'class_id'=>$param['class_id'], 'object'=>['in', $aObject]])
                ->count();
            if (!$materialCount) {
                $this->error('有不存在的object');
            }

            // 删除本地记录
            Db::name('oss_material')
                ->where(['user_id'=>$userId, 'class_id'=>$param['class_id'], 'object'=>['in', $aObject]])
                ->delete();

            // 删除阿里云OSS数据
            if (AliyunOssModule::delObjects($aObject) === false) {
                Log::write(sprintf('%s：删除OSS object失败：%s，失败原因：%s', __METHOD__, var_export($aObject, true), AliyunOssModule::$errMessage),'error');
            }

            $this->success('删除成功');

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 文件上传回调
     */
    public function uploadObjectCallback()
    {
        $userId = $this->getUserId();

        try {
            # 参数校验
            $validate = new Validate([
                'class_id'=> 'require', // 作用类别 1:个人相册 2:个人视频 3:个人语音介绍 4:达人认证 5:实名认证 6:个人头像 11:动态图片 12:动态视频 21:直播间封面
                'bucket' => 'require',
                'object' => 'require',
//                'etag' => 'require',
                'size' => 'require', // 文件大小
                'mime_type' => 'require|in:image,video,audio', // 类型 image,video,audio
                'video_cover_img' => 'requireIf:mime_type,video',
            ]);

            $validate->message([
                'class_id.require' => '请输入class_id!',
                'bucket.require' => '请输入bucket!',
                'object.require' => '请输入object!',
//                'etag.require' => '请输入etag!',
                'mime_type.require' => '请输入类型!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                // 调用参数日志记录
//                Log::write(sprintf('%s，文件上传回调，参数错误：%s', __METHOD__, $validate->getError()),'error');
                $this->error($validate->getError());
            }

            # 检测资源是否存在
            $materialCount = Db::name('oss_material')->where('object', $param['object'])->count();
            if ($materialCount) {
//                Log::write(sprintf('%s，文件上传回调，该资源已经存在，object：%s ', __METHOD__, $param['object']),'error');
                $this->error('该资源已经存在');
            }

            # 新增OSS资源
            $input = [
                'user_id' => $userId,
                'class_id' => $param['class_id'],
                'bucket' => $param['bucket'],
                'object' => $param['object'],
                'size' => $param['size'],
                'mime_type' => $param['mime_type'],
                'etag' => !empty($param['etag']) ? $param['etag'] : '',
                'extra_info' => !empty($param['extra_info']) ? $param['extra_info'] : '',
                'video_cover_img' => !empty($param['video_cover_img']) ? $param['video_cover_img'] : '',
                'image_info' => !empty($param['image_info']) ? $param['image_info'] : '',
                'video_info' => !empty($param['video_info']) ? $param['video_info'] : '',
                'audio_info' => !empty($param['audio_info']) ? $param['audio_info'] : '',
                'create_time' => time()
            ];

            $addId = Db::name('oss_material')->insertGetId($input);

            if ($addId) {
                // 成功
                $materialFind = Db::name('oss_material')->find($addId);

                $fullUrl = MaterialModule::getFullUrl($materialFind['object']);
                $aRet = [
                    'id' => $materialFind['id'],
                    'user_id' => $materialFind['user_id'],
                    'full_url' => $fullUrl,
                    'bucket' => $materialFind['bucket'],
                    'object' => $materialFind['object'],
                ];
                // 为视频生成封面图访问url
                if ($materialFind['mime_type'] == 'video') {
                    $aRet['cover_img'] = MaterialModule::getFullUrl($materialFind['video_cover_img']);
                }

                $this->success('OK', $aRet);
            } else {
                // 失败
                $this->error('失败');
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 批量删除文件资源
     */
    public function delObjects()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'objects' => 'require', // 多个用英文逗号分割
                'class_id' => 'require|integer'
            ]);

            $validate->message([
                'objects.require' => '请输入要删除的object!',
                'class_id.require'  => '请输入class_id！',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $aObject = explode(',', $param['objects']);
            foreach ($aObject as $i=>$item) {
                if (empty($item))
                    unset($aObject[$i]);
            }

            // 查询是否存在
            $materialCount = Db::name('oss_material')
                ->where(['user_id' => $userId, 'class_id' => $param['class_id'], 'object' => ['in', $aObject]])
                ->count();
            if (!$materialCount) {
                $this->error('有不存在的object');
            }

            // 批量删除数据
            if (MaterialModule::delMultipleObject($aObject) === false) {
                Log::write(sprintf('%s：删除文件资源失败：%s，失败原因：%s', __METHOD__, var_export($aObject, true), AliyunOssModule::$errMessage),'error');
                $this->error('删除失败，请重新操作');
            }

            $this->success('删除成功');

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
