<?php
/**
 * User: coase
 * Date: 2018-11-02
 * Time: 17:09
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\model\UserLikeModel;
use api\app\module\MaterialModule;
use api\app\module\promotion\InviteModule;

/**
 * #####视频操作功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.热门视频列表
 * 2.最新视频列表
 * 3.视频点赞
 * ``````````````````
 */
class VideoController extends RestUserBaseController
{
    /**
     * 热门视频列表，按照点赞数排序
     */
    public function popularVideo()
    {
        try {
            $iPage = $this->request->param('page', 1, 'intval'); //当前页
            $iPageSize = 15;
            $userId = $this->getUserId();

            # 获取列表数据
            $result = Db::name('oss_material')
                ->alias('m')
                ->join('user u', 'u.id = m.user_id  AND u.daren_status = 2')
                ->where("m.status=2")
                ->where("m.class_id=2") // 12:动态视频 todo 为苹果审核
                ->where("m.user_id <> {$userId}")
                ->group('m.user_id')
                ->field('m.*,u.avatar,u.user_nickname,u.signature,u.user_type')
                ->order('m.like_num', 'desc')
                ->paginate($iPageSize, false, ['page' => $iPage, 'list_rows' => $iPageSize])
                ->toArray();

            if (!$result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result['data'] as $row) {
                // 是否点赞
                $liked = Db::name('user_like')
                    ->where(['user_id'=>$userId, 'object_id'=>$row['id'], 'table_name'=>'oss_material', 'status'=>1])
                    ->count();

                // 获取用户的运营客服，如果不是机器人，则该值就是用户的id
                if ($row['user_type'] == 3) {
                    $prom_custom_uid = InviteModule::getInvitedUidByUId($userId);
                    if (empty($prom_custom_uid)) {
                        $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $row['user_id'])->value('custom_id');
                    }
                }

                // 获取视频全路径
                $fullUrl = MaterialModule::getFullUrl($row['object']);
                $aRet[] = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'prom_custom_uid' => !empty($prom_custom_uid) ? $prom_custom_uid : $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'signature' => $row['signature'],
                    'avatar' => MaterialModule::getFullUrl($row['avatar']),
                    'full_url' => $fullUrl,
                    'cover_img' => MaterialModule::getFullUrl($row['video_cover_img']),
                    'like_num' => $row['like_num'],
                    'look_num' => $row['look_num'],
                    'is_like' => !empty($liked) ? 1 : 0,
                ];
            }

            $this->success("OK", ['list' => $aRet, 'total_page' => $result['last_page']]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 最新视频列表
     */
    public function newVideo()
    {
        try {
            $iPage = $this->request->param('page', 1, 'intval'); //当前页
            $iPageSize = 15;
            $userId = $this->getUserId();

            # 获取列表数据
            $result = Db::name('oss_material')
                ->alias('m')
                ->join('user u', 'u.id = m.user_id AND u.daren_status = 2')
                ->where("m.status=2")
                ->where("m.class_id=2") // 12:动态视频  todo 为苹果审核
                ->where("m.user_id <> {$userId}")
                ->group('m.user_id')
                ->field('m.*,u.avatar,u.user_nickname,u.signature,u.user_type')
                ->order('m.create_time', 'desc')
                ->paginate($iPageSize, false, ['page' => $iPage, 'list_rows' => $iPageSize])
                ->toArray();

            if (!$result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result['data'] as $row) {
                // 是否点赞
                $liked = Db::name('user_like')->where(['user_id' => $userId, 'object_id' => $row['id'], 'table_name' => 'oss_material', 'status' => 1])->count();

                // 获取用户的运营客服，如果不是机器人，则该值就是用户的id
                if ($row['user_type'] == 3) {
                    $prom_custom_uid = InviteModule::getInvitedUidByUId($userId);
                    if (empty($prom_custom_uid)) {
                        $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $row['user_id'])->find();
                    }
                }

                // 获取视频全路径
                $fullUrl = MaterialModule::getFullUrl($row['object']);
                $aRet[] = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'prom_custom_uid' => !empty($prom_custom_uid) ? $prom_custom_uid : $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'signature' => $row['signature'],
                    'avatar' => MaterialModule::getFullUrl($row['avatar']),
                    'full_url' => $fullUrl,
                    'cover_img' => MaterialModule::getFullUrl($row['video_cover_img']),
                    'like_num' => $row['like_num'],
                    'look_num' => $row['look_num'],
                    'is_like' => !empty($liked) ? 1 : 0,
                ];
            }

            $this->success("OK", ['list' => $aRet, 'total_page' => $result['last_page']]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 视频点赞
     */
    public function doLike()
    {
        try {
            $userId = $this->getUserId();
            $materialId = $this->request->param('id', 0, 'int');

            $userLikeModel = new UserLikeModel();

            $findLikeCount = $userLikeModel->where([
                'user_id'   => $userId,
                'object_id' => $materialId
            ])->where('table_name', 'oss_material')->count();

            if (empty($findLikeCount)) {
                $material = Db::name('oss_material')->where('id', $materialId)->field('id,user_id,bucket,object')->find();
                if (empty($material)) {
                    $this->error('资源不存在！');
                }

                Db::startTrans();
                try {
                    Db::name('oss_material')->where(['id' => $materialId])->setInc('like_num');
                    $userLikeModel->insert([
                        'user_id'     => $userId,
                        'object_id'   => $materialId,
                        'table_name'  => 'oss_material',
                        'create_time' => time(),
                        'title'       => $material['object'],
                    ]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw new Exception($e->getMessage());
                }

                $likeCount = Db::name('oss_material')->where('id', $materialId)->value('like_num');
                $this->success("OK", ['like_num' => $likeCount]);
            } else {
                $this->error("您已赞过啦！");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 视频查看
     */
    public function doLook()
    {
        try {
            $validate = new Validate([
                'id' => 'require',
            ]);

            $validate->message([
                'id.require' => '参数错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $materialIds = explode(',', $param['id']);

            $materiaSelect = DB::name('oss_material')->whereIn('id', $materialIds)->select();

            if (empty($materiaSelect)) {
                $this->error('资源不存在！');
            } else {
                Db::name('oss_material')->whereIn('id', $materialIds)->setInc('look_num');
                $this->success();
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
