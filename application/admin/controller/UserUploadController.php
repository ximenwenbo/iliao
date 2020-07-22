<?php
/**
 * 后台用户上传管理
 * @author coase
 */
namespace app\admin\controller;

use app\admin\service\file\UploadService;
use app\admin\service\MaterialService;
use app\admin\service\LogsService;
use app\admin\service\RbacRoleService;
use app\admin\service\RoleUserService;
use app\admin\service\UserActionRecordService;
use app\admin\service\UserMemberService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;

class UserUploadController extends AdminBaseController
{
    /**
     *  上传
     *  @throws
     */
    public function uploadFile()
    {
        $param = $this->request->file();

        $userId = $this->request->param('user_id', 0);
        $delObject = $this->request->param('del_object', '');

        if (isset($param['avatar'])) {
            $file = $this->request->file('avatar');
            $classId = 6; // 个人头像
            $mimeType = 'image';
        } elseif (isset($param['album'])) {
            $file = $this->request->file('album');
            $classId = 1; // 个人相册
            $mimeType = 'image';
        } elseif (isset($param['video'])) {
            $file = $this->request->file('video');
            $classId = 2; // 个人视频
            $mimeType = 'video';
        } elseif (isset($param['icon'])) {
            $file = $this->request->file('icon');
            $classId = 22; // 直播间频道图标
            $mimeType = 'icon';
        } elseif (isset($param['thumb'])) {
            $file = $this->request->file('thumb');
            $classId = 23; // 缩略图
            $mimeType = 'thumb';
        } elseif (isset($param['gift_thumb'])) {
            $file = $this->request->file('gift_thumb');
            $classId = 24; // 礼物图
            $mimeType = 'gift';
        } elseif (isset($param['gift_gif'])) {
            $file = $this->request->file('gift_gif');
            $classId = 25; // 礼物图
            $mimeType = 'gift';
        } elseif (isset($param['banner'])) {
            $file = $this->request->file('banner');
            $classId = 26; // banner轮播图
            $mimeType = 'banner';
        } elseif (isset($param['android_config'])) {
            $file = $this->request->file('android_config');
            $classId = 27;//Android下载二维码
            $mimeType = 'android_code';
        } elseif (isset($param['iPhone_config'])) {
            $file = $this->request->file('iPhone_config');
            $classId = 28; // IOS下载二维码
            $mimeType = 'iPhone_code';
        } elseif (isset($param['share_background_img'])) {
            $file = $this->request->file('share_background_img');
            $classId = 29; // 分享页面背景图
            $mimeType = 'share_background_img';
        } elseif (isset($param['share_logo'])) {
            $file = $this->request->file('share_logo');
            $classId = 30; // 分享页面logo
            $mimeType = 'share_logo';
        } else {
            return json_encode(['code'=>0, 'msg'=>'网络异常']);
        }

        $fileExt = $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $fileTmp = $file->getInfo('tmp_name');
        $newName = md5(microtime() . mt_rand(1, 9999)) . '.' . $fileExt;
        switch ($mimeType){
            case 'gift':
                $newObject = 'assets/gift/'  . $newName;
                break;
            case 'banner':
                $newObject = 'assets/banner/'  . $newName;
                break;
            default:
                $newObject = 'upload/' . date("Ymd") . '/' . $newName;
                break;
        }
        $res = UploadService::uploadObject(fopen($fileTmp, 'rb'), $newObject);

        if ($res != false) {
            // 删除文件
            if ($delObject) {
                UploadService::delObject($delObject);
                Db::name('oss_material')->where('object', $delObject)->delete();
            }

            // 获取腾讯云TRTC配置
            $txyunOption = cmf_get_option('trtc');
            // 新的资源表数据
            $newMaterial = [
                'user_id' => $userId,
                'class_id' => $classId,
                'bucket' => $txyunOption['cosBucket'],
                'object' => $newObject,
                'mime_type' => $mimeType,
            ];
            if (! Db::name('oss_material')->where('object', $newObject)->count()) {
                $newMaterial['create_time'] = time();
                Db::name('oss_material')->insert($newMaterial);
            } else {
                $newMaterial['update_time'] = time();
                Db::name('oss_material')->where('object', $newObject)->update($newMaterial);
            }

            $data = [
                'type' => 1,
                'save_name' => $newName,
                'save_dir' => $newObject,
                'abs_path' => MaterialService::getFullUrl($newObject),
            ];
            return json_encode(['code'=>1, 'data'=>$data]);
        } else {
            return json_encode(['code'=>0, 'msg'=>'上传失败']);
        }

    }

    /**
     * 删除
     */
    public function deleteFile()
    {
        $object = $this->request->param('object', '');

        if (! $object) {
            return json_encode(['code'=>0, 'msg'=>'没有需要删除的资源']);
        }
        $res = UploadService::delObject($object);

        if ($res != false) {
            if (Db::name('oss_material')->where('object', $object)->count()) {
                Db::name('oss_material')->where('object', $object)->delete();
            }

            return json_encode(['code'=>1, 'data'=>[]]);
        } else {
            return json_encode(['code'=>0, 'msg'=>'上传失败']);
        }

    }

}
