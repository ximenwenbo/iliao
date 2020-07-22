<?php
/**
 * User: coase
 * Date: 2019-02-14
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\forum\ForumModule;
use api\app\module\forum\CircleModule;
use api\app\module\txyun\YuntongxinModule;
use api\app\module\promotion\InviteModule;
use api\app\module\ConfigModule;

/**
 * #####社区的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.新增动态
 * 2.新增回复
 * 3.推荐动态
 * 4.附近动态
 * ``````````````````
 */
class ForumController extends RestBaseController
{
    /**
     * 新增动态
     * @author coase
     */
    public function addDynamic()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'type' => 'require|in:1,2', // 类型（1:心情 2:帖子-标题和内容必填）
                'title' => 'requireIf:type,2', // 标题,当类型为2时，必传
                'content' => 'requireIf:type,2', // 内容,当类型为2时，必传
                'picture' => 'length:1,1000', // 图片uri，逗号分隔
                'video' => 'length:1,100', // 视频uri，逗号分隔
                'province_name' => 'length:1,100', // 省份名称
                'city_name' => 'length:1,100', // 城市名称
                'circle_id' => 'integer', // 圈子ID
            ]);

            $validate->message([
                'type.require' => '请输入类型!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userInfo = Db::name("user")->find($userId);
            if ($userInfo['daren_status'] !== 2) {
//                $this->error('您目前还不能发表动态');
            }

            if ($param['type'] == 1) {
                unset($param['content']);
            }

            # 新增动态
            $result = ForumModule::addDynamic($userId, $param);

            if ($result) {
                $this->success("OK", []);
            } else {
                $this->error(ForumModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 新增动态回复
     * @author Coase
     */
    public function addReply4Dynamic()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'dynamic_id' => 'require|integer', // 动态ID
                'parent_reply_id' => 'require|integer', // 父级回复ID
                'reviewed_user_id' => 'require|integer', // 被答复人UID
                'content' => 'require', // 回复内容
            ]);

            $validate->message([
                'dynamic_id.require' => '请输入ID!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $dynamicFind = Db::name('forum_dynamic')->where('id', $param['dynamic_id'])->find();
            if (empty($dynamicFind)) {
                $this->error('数据不存在');
            }
            if ($dynamicFind['status'] == 0) {
                $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
            }

            # 新增回复
            $replyId = ForumModule::addReply4DynamicGetId($userId, $param);

            if ($replyId) {
                // 新增app消息
                if (empty($param['parent_reply_id'])) { // 一级回复
                    $type = 31; // 动态的回复
                    $objectId = $dynamicFind['id'];
                    $objectUserId = $dynamicFind['user_id'];
                    $title = $dynamicFind['title'];
                    if ($dynamicFind['picture']) {
                        $aPicture = ForumModule::formatOssReturn($dynamicFind['picture']);
                        $picture = !empty($aPicture[0]['thumb_img']) ? $aPicture[0]['thumb_img'] : '';
                    } elseif ($dynamicFind['video']) {
                        $aVideo = ForumModule::formatOssReturn($dynamicFind['video']);
                        $video = !empty($aVideo[0]['cover_img']) ? $aVideo[0]['cover_img'] : '';
                    }
                } else { // 二级回复
                    $type = 32; // 回复的回复
                    $replyFind = Db::name('forum_reply')->where('id', $param['parent_reply_id'])->find();
                    if (empty($replyFind) || $replyFind['status'] == 0) {
                        $this->error('回复不存在或者被删除');
                    }
                    $objectId = $replyFind['id'];
                    $objectUserId = $replyFind['user_id'];
                    $title = $replyFind['content'];
                }
                $msgParam = [
                    'by_user_id' => $userId,
                    'by_object_id' => $replyId,
                    'type' => $type,
                    'object_id' => $objectId,
                    'object_user_id' => $objectUserId,
                    'title'       => isset($title) ? $title : '',
                    'img_thumb'   => isset($picture) ? $picture : '',
                    'video_cover' => isset($video) ? $video : '',
                    'create_time' => time()
                ];
                Db::name('forum_msg')->insert($msgParam);

                if (Db::name('user')->where('id', $objectUserId)->value('user_type') == 2) {
                    // 发送云通信消息
                    YuntongxinModule::pushForumMsg($objectUserId, 'reply');
                }

                $this->success("OK");
            } else {
                $this->error(ForumModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 搜索动态
     * @author coase
     */
    public function searchDynamic()
    {
        try {
            $validate = new Validate([
                'keyword' => 'require|max:25',
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'keyword.require' => '请输入关键词!',
                'keyword.max' => '搜索词长度不能超过25个字符!',
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $keyword = $param['keyword'];
            $iPage = $param['page'];
            $iPageSize = 10;

            if ($this->userId) {
                // 获取拉黑的用户id
                $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('r.action', 'NEW')
                    ->where('d.title LIKE "%'.$keyword.'%" OR d.content LIKE "%'.$keyword.'%"')
                    ->whereNotIn('d.user_id', $aBlockedUid)
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            } else {
                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('r.action', 'NEW')
                    ->where('d.title LIKE "%'.$keyword.'%" OR d.content LIKE "%'.$keyword.'%"')
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            }

            if (! $result) {
                $this->error('数据为空');
            }
            $list = $this->formatDynamicList($result);

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 推荐动态
     * @author coase
     */
    public function popularDynamic()
    {
        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            if ($this->userId) {
                // 获取拉黑的用户id
                $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW')
                    ->whereNotIn('d.user_id', $aBlockedUid)
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            } else {
                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW')
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            }

            if (! $result) {
                $this->error('数据为空');
            }
            $list = $this->formatDynamicList($result);

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 附近动态
     * @author coase
     */
    public function vicinityDynamic()
    {
        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000',
                'city_name' => 'require|min:1', // 城市名称，不要加"市"
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            if ($this->userId) {
                // 获取拉黑的用户id
                $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW')
                    ->whereLike('d.city_name', $param['city_name'] . '%')
                    ->whereNotIn('d.user_id', $aBlockedUid)
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            } else {
                $result = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                    ->join('user u', 'u.id = d.user_id')
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW')
                    ->whereLike('d.city_name', $param['city_name'] . '%')
                    ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                    ->order('d.create_time', 'desc')
                    ->page($iPage, $iPageSize)
                    ->select();
            }

            if (! $result) {
                $this->error('数据为空');
            }
            $list = $this->formatDynamicList($result);

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 关注动态列表
     * @author coase
     */
    public function followDynamic()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000',
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 10;

            // 获取拉黑的用户id
            $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

            $followUids = Db::name('user_follow')
                ->alias('f')
                ->join('user u', 'u.id = f.be_user_id')
                ->where("f.user_id={$userId}")
                ->whereNotIn('f.be_user_id', $aBlockedUid)
                ->where("f.status=1")
                ->column('f.be_user_id');

            $result = Db::name('forum_opt_record')
                ->alias('r')
                ->join('forum_dynamic d', 'd.id = r.object_id AND d.status = 2')
                ->join('user u', 'u.id = d.user_id')
                ->where('d.y_level <= ' . $this->yLevel)
                ->where('r.action', 'NEW')
                ->whereIn('d.user_id', $followUids)
                ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                ->order('d.create_time', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $result) {
                $this->error('数据为空');
            }
            $list = $this->formatDynamicList($result);

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 视频动态
     * @author coase
     */
    public function videoDynamic()
    {
        try {
            $validate = new Validate([
                'dynamic_id' => 'integer',
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'dynamic_id.integer' => '参数dynamic_id有误!',
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (! empty($param['dynamic_id'])) {
                if (! Db::name('forum_dynamic')->where(['id' => $param['dynamic_id'], 'status' => 2])->count()) {
                    $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
                }
            }

            $userId = $this->userId;
            $iPage = $param['page'];
            $iPageSize = 16;


            if ($this->userId) {
                // 获取拉黑的用户id
                $aBlockedUid = Db::name('user_block_record')->where('user_id', $this->userId)->column('be_user_id');

                $query = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', "d.id = r.object_id AND d.status = 2 AND d.video != '' ")
                    ->join('user u', 'u.id = d.user_id')
                    ->whereNotIn('d.user_id', $aBlockedUid)
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW');
            } else {
                $query = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_dynamic d', "d.id = r.object_id AND d.status = 2 AND d.video != '' ")
                    ->join('user u', 'u.id = d.user_id')
                    ->where('d.y_level <= ' . $this->yLevel)
                    ->where('r.action', 'NEW');
            }

            if (! empty($param['dynamic_id'])) {
                $query = $query->where('d.id <= ' . $param['dynamic_id']);
            }

            $result = $query->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status')
                ->order('d.create_time', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $result) {
                $this->error('数据为空');
            }

            // 获取圈子列表数据
            $circleMapList = CircleModule::getCircleList();

            $list = [];
            foreach ($result as $row) {
                if ($userId) {
                    // 是否点赞
                    $liked = Db::name('user_like')
                        ->where(['user_id' => $userId, 'object_id' => $row['id'], 'table_name' => 'forum_dynamic', 'status' => 1])
                        ->count();

                    // 是否关注
                    $isFollow = Db::name('user_follow')->where(['user_id'=>$userId, 'be_user_id'=>$row['user_id'], 'status'=>1])->count();
                } else {
                    $liked = 0;
                    $isFollow = 0;
                }

                // 获取用户的运营客服，如果不是机器人，则该值就是用户的id
                if (Db::name('user')->where('id', $row['user_id'])->value('user_type') == 3) {
                    $prom_custom_uid = InviteModule::getInvitedUidByUId($userId);
                    if (empty($prom_custom_uid)) {
                        $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $row['user_id'])->value('custom_id');
                    }
                }

                // 一级回复数量
                $replyCount = Db::name('forum_opt_record')->alias('r')
                    ->join('forum_reply p', 'p.id = r.reply_id AND p.status = 2')
                    ->where(['r.object_id' => $row['id'],'r.parent_reply_id' => 0, 'r.action' => 'REPLY'])
                    ->count();

                $list[] = [
                    'dynamic_id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'avatar' => $row['avatar'],
                    'daren_status' => $row['daren_status'],
                    'sex' => $row['sex'],
                    'city_name' => $row['city_name'],
                    'type' => $row['type'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'picture_list' => ForumModule::formatOssReturn($row['picture']),
                    'video_list' => ForumModule::formatOssReturn($row['video']),
                    'circle_id' => $row['circle_id'],
                    'circle_title' => !empty($circleMapList[$row['circle_id']]['name']) ? $circleMapList[$row['circle_id']]['name'] : '',
                    'is_like' => !empty($liked) ? 1 : 0,
                    'like_num' => $row['like_num'],
                    'reply_num' => $replyCount, // 一级回复的总数量
                    'publish_time' => ForumModule::timeTran($row['create_time']),
                    'is_vip' => \api\app\module\VipModule::checkIsVip($row['vip_expire_time']),
                    'is_follow' => $isFollow ? 1 : 0,
                    'prom_custom_uid' => !empty($prom_custom_uid) ? $prom_custom_uid : $row['user_id']
                ];
            }

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 根据用户id获取动态列表--用户中心页面显示
     * @author coase
     */
    public function dynamicListByUid()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer',
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'user_id.require' => '请输入user_id!',
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $param['user_id'];
            $iPage = $param['page'];
            $iPageSize = 10;

            $result = Db::name('forum_dynamic')
                ->alias('d')
                ->join('user u', 'u.id = d.user_id AND d.status = 2')
                ->where('d.user_id', $userId)
                ->where('d.y_level <= ' . $this->yLevel)
                ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,u.daren_status,u.virtual_pos')
                ->order('d.create_time', 'desc')
                ->page($iPage, $iPageSize)
                ->select();

            if (! $result) {
                $this->error('数据为空');
            }
            $list = $this->formatDynamicList($result);

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 动态详情
     * @author coase
     */
    public function detailDynamic()
    {
        try {
            $validate = new Validate([
                'dynamic_id' => 'require|integer' // 动态id
            ]);

            $validate->message([
                'dynamic_id.require' => '请输入id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->userId;

            $dynamicRow = Db::name('forum_dynamic')
                ->alias('d')
                ->join('user u', 'u.id = d.user_id')
                ->where('d.id', $param['dynamic_id'])
                ->field('d.*,u.user_nickname,u.avatar,u.sex,u.vip_expire_time,daren_status')
                ->find();
            if (empty($dynamicRow)) {
                $this->error('数据为空');
            }
            if ($dynamicRow['status'] == 0) {
                $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
            }

            // 是否点赞
            $liked = Db::name('user_like')
                ->where(['user_id' => $userId, 'object_id' => $param['dynamic_id'], 'table_name' => 'forum_dynamic', 'status' => 1])
                ->count();

            // 获取动态的一级回复数量
            $replyCount = Db::name('forum_opt_record')
                ->where(['object_id' => $dynamicRow['id'], 'parent_reply_id' => 0, 'action' => 'REPLY'])
                ->count();

            // 获取圈子列表数据
            $circleMapList = CircleModule::getCircleList();

            $ret = [
                'dynamic_id' => $dynamicRow['id'],
                'user_id' => $dynamicRow['user_id'],
                'user_nickname' => $dynamicRow['user_nickname'],
                'avatar' => $dynamicRow['avatar'],
                'daren_status' => $dynamicRow['daren_status'],
                'sex' => $dynamicRow['sex'],
                'city_name' => $dynamicRow['city_name'],
                'type' => $dynamicRow['type'],
                'title' => $dynamicRow['title'],
                'content' => $dynamicRow['content'],
                'picture_list' => ForumModule::formatOssReturn($dynamicRow['picture']),
                'video_list' => ForumModule::formatOssReturn($dynamicRow['video']),
                'circle_id' => $dynamicRow['circle_id'],
                'circle_title' => !empty($circleMapList[$dynamicRow['circle_id']]['name']) ? $circleMapList[$dynamicRow['circle_id']]['name'] : '',
                'is_like' => !empty($liked) ? 1 : 0,
                'like_num' => $dynamicRow['like_num'],
                'reply_num' => $replyCount,
                'is_vip' => VipModule::checkIsVip($dynamicRow['vip_expire_time']),
            ];

            // 获取用户的运营客服，如果不是机器人，则该值就是用户的id
            if (Db::name('user')->where('id', $dynamicRow['user_id'])->value('user_type') == 3) {
                $prom_custom_uid = InviteModule::getInvitedUidByUId($userId);
                if (empty($prom_custom_uid)) {
                    $prom_custom_uid = Db::name('allot_robot')->where('robot_id', $dynamicRow['user_id'])->value('custom_id');
                }
            }

            // 是否关注
            if (! empty($userId)) {
                $isFollow = Db::name('user_follow')->where(['user_id' => $userId, 'be_user_id' => $dynamicRow['user_id'], 'status' => 1])->count();
            } else {
                $isFollow = 0;
            }

            $this->success("OK", [
                'detail' => $ret,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                    'is_follow' => $isFollow ? 1 : 0,
                    'prom_custom_uid' => !empty($prom_custom_uid) ? $prom_custom_uid : $dynamicRow['user_id']
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 动态的回复列表
     * @author coase
     */
    public function replayList4Dynamic()
    {
        try {
            $validate = new Validate([
                'dynamic_id' => 'require|integer',
                'type' => 'require|in:1,2,3', //查看类型 1:正序查看 2:倒序查看 3:只看楼主
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->userId;
            $iPage = $param['page'];
            $iPageSize = 10;

            // 校验动态是否存在
            $dynamicFind = Db::name('forum_dynamic')->field('id,user_id,status')->find($param['dynamic_id']);
            if (! $dynamicFind) {
                $this->error('数据为空');
            }
            if ($dynamicFind['status'] == 0) {
                $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
            }

            $query = Db::name('forum_opt_record')
                ->alias('r')
                ->join('forum_reply p', 'p.id = r.reply_id and p.status = 2')
                ->join('user u', 'u.id = p.user_id')
                ->where('r.action', 'REPLY')
                ->where('r.object_id', $param['dynamic_id'])
                ->where('r.parent_reply_id', 0)
                ->field('p.*,r.object_user_id,r.reply_floor,u.user_nickname,u.avatar,u.sex,u.daren_status,u.vip_expire_time');

            if ($param['type'] == 1) {
                $query->order('p.create_time', 'asc');
            } elseif ($param['type'] == 2) {
                $query->order('p.create_time', 'desc');
            } else {
                $query->where('r.user_id', $dynamicFind['user_id'])->order('p.create_time', 'asc');
            }

            $result = $query->page($iPage, $iPageSize)->select();
            if (! $result) {
                $this->error('数据为空');
            }
            $aRet = [];
            foreach ($result as $row) {
                // 是否点赞
                if (! empty($userId)) {
                    $liked = Db::name('user_like')
                        ->where(['user_id' => $userId, 'object_id' => $row['id'], 'table_name' => 'forum_reply', 'status' => 1])
                        ->count();
                } else {
                    $liked = 0;
                }

                // 获取回复的回复
                $replyPaginate = Db::name('forum_opt_record')
                    ->alias('r')
                    ->join('forum_reply p', 'p.id = r.reply_id and p.status = 2')
                    ->join('user u', 'u.id = p.user_id')
                    ->where('r.action', 'REPLY')
                    ->where('r.object_id', $param['dynamic_id'])
                    ->where('r.parent_reply_id', $row['id'])
                    ->field('p.id reply_id,r.object_user_id,p.content,p.user_id,p.reviewed_user_id,p.create_time,u.user_nickname,u.sex')
                    ->paginate(2)
                    ->toArray();

                $replyTotal = $replyPaginate['total'];
                $replyList = [];

                foreach ($replyPaginate['data'] as $k => $item) {
                    $replyList[$k] = [
                        'reply_id' => $item['reply_id'],
                        'content' => $item['content'],
                        'user_id' => $item['user_id'],
                        'user_nickname' => $item['user_nickname'],
                        'sex' => $item['sex'],
                        'is_lz' => ($item['object_user_id'] == $item['user_id']) ? 1 : 0,
                        'reviewed_user_id' => $item['reviewed_user_id'],
                    ];
                    if (! empty($item['reviewed_user_id'])) {
                        $replyList[$k]['reviewed_user_nickname'] = Db::name('user')->where('id',$item['reviewed_user_id'])->value('user_nickname');
                    } else {
                        $replyList[$k]['reviewed_user_nickname'] = '';
                    }
                }

                $aRet[] = [
                    'reply_id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'user_nickname' => $row['user_nickname'],
                    'avatar' => $row['avatar'],
                    'sex' => $row['sex'],
                    'content' => $row['content'],
                    'reply_list' => [
                        'total' => $replyTotal,
                        'list' => $replyList
                    ],
                    'is_like' => !empty($liked) ? 1 : 0,
                    'like_num' => $row['like_num'],
                    'is_vip' => VipModule::checkIsVip($row['vip_expire_time']),
                    'reply_time' => ForumModule::timeTran($row['create_time']),
                    'daren_status' => $row['daren_status'],
                    'floor_num' => $row['reply_floor'],
                    'is_lz' => ($row['object_user_id'] == $row['user_id']) ? 1 : 0,
                ];
            }

            $this->success("OK", [
                'list' => $aRet,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 回复的回复列表
     * @author coase
     */
    public function replayList4Reply()
    {
        try {
            $validate = new Validate([
                'reply_id' => 'require|integer',
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->userId;
            $iPage = $param['page'];
            $iPageSize = 30;

            // 回复的详细内容
            if ($iPage == 1) {
                $result = Db::name('forum_reply')
                    ->alias('p')
                    ->join('forum_opt_record r', 'r.reply_id = p.id')
                    ->join('user u', 'u.id = p.user_id')
                    ->where('r.action', 'REPLY')
                    ->field('p.*,r.object_id,r.object_user_id,r.reply_floor,u.user_nickname,u.avatar,u.sex,u.daren_status,u.vip_expire_time')
                    ->find($param['reply_id']);
                if (! $result) {
                    $this->error('数据为空');
                }
                if ($result['status'] == 0) {
                    $this->error(['code' => 2202, 'msg' => '回复已经被删除']);
                }

                // 是否点赞
                if (! empty($userId)) {
                    $liked = Db::name('user_like')
                        ->where(['user_id' => $userId, 'object_id' => $param['reply_id'], 'table_name' => 'forum_reply', 'status' => 1])
                        ->count();
                } else {
                    $liked = 0;
                }

                $detail = [
                    'reply_id' => $result['id'],
                    'dynamic_id' => $result['object_id'],
                    'user_id' => $result['user_id'],
                    'user_nickname' => $result['user_nickname'],
                    'avatar' => $result['avatar'],
                    'sex' => $result['sex'],
                    'content' => $result['content'],
                    'is_like' => !empty($liked) ? 1 : 0,
                    'like_num' => $result['like_num'],
                    'is_vip' => VipModule::checkIsVip($result['vip_expire_time']),
                    'daren_status' => $result['daren_status'],
                    'floor_num' => $result['reply_floor'],
                    'reply_time' => ForumModule::timeTran($result['create_time']),
                    'is_lz' => ($result['object_user_id'] == $result['user_id']) ? 1 : 0,
                ];
            } else {
                $detail = (object) null;
            }

            // 回复的回复列表
            $replyList = Db::name('forum_opt_record')
                ->alias('r')
                ->join('forum_reply p', 'p.id = r.reply_id and p.status = 2')
                ->join('user u', 'u.id = p.user_id')
                ->where('r.action', 'REPLY')
                ->where('r.parent_reply_id', $param['reply_id'])
                ->field('p.id reply_id,r.object_id dynamic_id,r.object_user_id,p.user_id,p.content,p.reviewed_user_id,u.user_nickname,u.sex')
                ->page($iPage, $iPageSize)
                ->select()
                ->toArray();

            foreach ($replyList as &$item) {
                if (! empty($item['reviewed_user_id'])) {
                    $item['reviewed_user_nickname'] = Db::name('user')->where('id',$item['reviewed_user_id'])->value('user_nickname');
                } else {
                    $item['reviewed_user_nickname'] = '';
                }
                $item['is_lz'] = ($item['object_user_id'] == $item['user_id']) ? 1 : 0;
                unset($item['object_user_id']);
            }

            $this->success("OK", [
                'detail' => $detail,
                'reply_list' => $replyList,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 点赞
     * @author coase
     */
    public function doLike()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'object_id' => 'require|integer',
                'type' => 'require|in:forum_dynamic,forum_reply'
            ]);

            $validate->message([
                'object_id.require' => '请输入object_id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $tableName = $param['type'];

            $objectFind = Db::name($tableName)->where('id', $param['object_id'])->find();
            if (! $objectFind) {
                $this->error('数据不存在！');
            }
            if ($objectFind['status'] == 0) {
                if ($tableName == 'forum_dynamic') {
                    $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
                } else {
                    $this->error(['code' => 2202, 'msg' => '回复已经被删除']);
                }
            }

            $likeFind = Db::name('user_like')
                ->where(['user_id' => $userId, 'object_id' => $param['object_id'], 'table_name' => $tableName])
                ->field('id, status')
                ->find();
            if (empty($likeFind)) {
                Db::startTrans();
                try {
                    Db::name($tableName)->where('id', $param['object_id'])->setInc('like_num');

                    $likeId = Db::name('user_like')->insertGetId([
                        'user_id'     => $userId,
                        'object_id'   => $param['object_id'],
                        'table_name'  => $tableName,
                        'create_time' => time(),
                    ]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw new Exception($e->getMessage());
                }

                // 新增app消息
                if ($tableName == 'forum_dynamic') {
                    $type = 11; // 社区动态点赞
                    $dynamicFind = Db::name($tableName)->where('id', $param['object_id'])->find();
                    $objectId = $dynamicFind['id'];
                    $objectUserId = $dynamicFind['user_id'];
                    $title = $dynamicFind['title'];
                    if ($dynamicFind['picture']) {
                        $aPicture = ForumModule::formatOssReturn($dynamicFind['picture']);
                        $picture = !empty($aPicture[0]['thumb_img']) ? $aPicture[0]['thumb_img'] : '';
                    } elseif ($dynamicFind['video']) {
                        $aVideo = ForumModule::formatOssReturn($dynamicFind['video']);
                        $video = !empty($aVideo[0]['cover_img']) ? $aVideo[0]['cover_img'] : '';
                    }
                } else {
                    $type = 12; // 社区回复点赞
                    $replyFind = Db::name($tableName)->where('id', $param['object_id'])->find();
                    $objectId = $replyFind['id'];
                    $objectUserId = $replyFind['user_id'];
                    $title = $replyFind['content'];
                }
                $msgParam = [
                    'by_user_id' => $userId,
                    'by_object_id' => $likeId,
                    'type' => $type,
                    'object_id' => $objectId,
                    'object_user_id' => $objectUserId,
                    'title'       => isset($title) ? $title : '',
                    'description' => isset($description) ? $description : '',
                    'img_thumb'   => isset($picture) ? $picture : '',
                    'video_cover' => isset($video) ? $video : '',
                    'create_time' => time()
                ];
                Db::name('forum_msg')->insert($msgParam);

                if (Db::name('user')->where('id', $objectUserId)->value('user_type') == 2) {
                    // 给真实用户发送云通信消息
                    YuntongxinModule::pushForumMsg($objectUserId, 'like');
                }

                $this->success("OK", [
                    'like_num' => Db::name($tableName)->where('id', $param['object_id'])->value('like_num')
                ]);
            } elseif ($likeFind['status'] == 0) {
                Db::startTrans();
                try {
                    Db::name($tableName)->where('id', $param['object_id'])->setInc('like_num');

                    Db::name('user_like')->where('id', $likeFind['id'])->update(['status' => 1, 'create_time' => time()]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw new Exception($e->getMessage());
                }

                $this->success("OK", [
                    'like_num' => Db::name($tableName)->where('id', $param['object_id'])->value('like_num')
                ]);
            } else {
                $this->error("您已经赞过啦");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 取消点赞
     * @author coase
     */
    public function undoLike()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'object_id' => 'require|integer',
                'type' => 'require|in:forum_dynamic,forum_reply'
            ]);

            $validate->message([
                'object_id.require' => '请输入object_id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $tableName = $param['type'];

            $objectFind = Db::name($tableName)->where('id', $param['object_id'])->find();
            if (! $objectFind) {
                $this->error('数据不存在！');
            }
            if ($objectFind['status'] == 0) {
                if ($tableName == 'forum_dynamic') {
                    $this->error(['code' => 2201, 'msg' => '动态已经被删除']);
                } else {
                    $this->error(['code' => 2202, 'msg' => '回复已经被删除']);
                }
            }

            $likeCount = Db::name('user_like')
                ->where(['user_id' => $userId, 'object_id' => $param['object_id'], 'table_name' => $tableName])
                ->count();

            if (! empty($likeCount)) {
                Db::startTrans();
                try {
                    Db::name($tableName)->where('id', $param['object_id'])->setDec('like_num');
                    Db::name('user_like')->where([
                        'user_id' => $userId,
                        'object_id' => $param['object_id'],
                        'table_name' => $tableName,
                    ])->update(['status' => 0]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw new Exception($e->getMessage());
                }

                $this->success("OK", [
                    'like_num' => Db::name($tableName)->where('id', $param['object_id'])->value('like_num')
                ]);
            } else {
                $this->error("您还没有赞过");
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 收到的点赞消息列表
     */
    public function likeMessageList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;

            $msgList = Db::name('forum_msg')
                ->alias('m')
                ->join('user u', 'u.id = m.by_user_id')
                ->whereIn('m.type', [11,12]) // 类型（11:社区评论点赞 12:社区回复点赞）
                ->where('m.object_user_id', $userId)
                ->where('m.status', 1)
                ->field('m.*,u.id by_user_id,u.user_nickname by_user_nickname,u.avatar by_avatar')
                ->order('m.create_time', 'desc')
                ->paginate($iPageSize, false, ['page' => $iPage])
                ->toArray();

            $aRet = [];
            foreach ($msgList['data'] as $k => $item) {
                $aRet[$k] = [
                    'type' => $item['type'],
                    'by_user_id' => $item['by_user_id'],
                    'by_user_nickname' => $item['by_user_nickname'],
                    'by_avatar' => $item['by_avatar'],
                    'object_id' => $item['object_id'],
                    'object_title' => $item['title'],
                    'img_thumb' => $item['img_thumb'],
                    'video_cover' => $item['video_cover'],
                    'create_time' => \api\app\module\ChatModule::timeTran($item['create_time']),
                ];
            }

            $this->success("OK", [
                'total' => $msgList['total'],
                'list' => $aRet,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 收到的回复消息列表
     */
    public function replyMessageList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;

            $msgList = Db::name('forum_msg')
                ->alias('m')
                ->join('user u', 'u.id = m.by_user_id')
                ->join('forum_reply r', 'r.id = m.by_object_id')
                ->whereIn('m.type', [31,32]) // 类型（31:社区评论的回复 32:社区回复的回复）
                ->where('m.object_user_id', $userId)
                ->where('m.status', 1)
                ->field('m.*,r.content,u.id by_user_id,u.user_nickname by_user_nickname,u.avatar by_avatar')
                ->order('m.create_time', 'desc')
                ->paginate($iPageSize, false, ['page' => $iPage])
                ->toArray();

            $aRet = [];
            foreach ($msgList['data'] as $k => $item) {
                $aRet[$k] = [
                    'type' => $item['type'],
                    'by_user_id' => $item['by_user_id'],
                    'by_user_nickname' => $item['by_user_nickname'],
                    'by_content' => $item['content'],
                    'by_avatar' => $item['by_avatar'],
                    'object_id' => $item['object_id'],
                    'object_title' => $item['title'],
                    'img_thumb' => $item['img_thumb'],
                    'video_cover' => $item['video_cover'],
                    'create_time' => \api\app\module\ChatModule::timeTran($item['create_time']),
                ];
            }

            $this->success("OK", [
                'total' => $msgList['total'],
                'list' => $aRet,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 清空点赞消息
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function clearLikeMessage()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        Db::name('forum_msg')
            ->whereIn('type', [11,12])
            ->where('object_user_id', $userId)
            ->where('status', 1)
            ->update(['status' => 2, 'update_time' => time()]);

        $this->success("OK");
    }

    /**
     * 清空回复消息
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function clearReplyMessage()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        Db::name('forum_msg')
            ->whereIn('type', [31,32])
            ->where('object_user_id', $userId)
            ->where('status', 1)
            ->update(['status' => 2, 'update_time' => time()]);

        $this->success("OK");
    }

    /**
     * 格式化动态列表数据
     * @author coase
     * @param array $aDynamic
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function formatDynamicList($aDynamic)
    {
        $userId = $this->userId;
        // 获取圈子列表数据
        $circleMapList = CircleModule::getCircleList();

        $aRet = [];
        foreach ($aDynamic as $row) {
            // 是否点赞
            if ($userId) {
                $liked = Db::name('user_like')
                    ->where(['user_id' => $userId, 'object_id' => $row['id'], 'table_name' => 'forum_dynamic', 'status' => 1])
                    ->count();
            } else {
                $liked = 0;
            }

            // 一级回复数量
            $replyCount = Db::name('forum_opt_record')->alias('r')
                ->join('forum_reply p', 'p.id = r.reply_id AND p.status = 2')
                ->where(['r.object_id' => $row['id'],'r.parent_reply_id' => 0, 'r.action' => 'REPLY'])
                ->count();
            $aRet[] = [
                'dynamic_id' => $row['id'],
                'user_id' => $row['user_id'],
                'user_nickname' => $row['user_nickname'],
                'avatar' => $row['avatar'],
                'daren_status' => $row['daren_status'],
                'sex' => $row['sex'],
                'city_name' => $row['virtual_pos']==1 ? '' : $row['city_name'],
                'type' => $row['type'],
                'title' => $row['title'],
                'content' => $row['content'],
                'picture_list' => ForumModule::formatOssReturn($row['picture']),
                'video_list' => ForumModule::formatOssReturn($row['video']),
                'circle_id' => $row['circle_id'],
                'circle_title' => !empty($circleMapList[$row['circle_id']]['name']) ? $circleMapList[$row['circle_id']]['name'] : '',
                'is_like' => !empty($liked) ? 1 : 0,
                'like_num' => $row['like_num'],
                'reply_num' => $replyCount, // 一级回复的总数量
                'publish_time' => ForumModule::timeTran($row['create_time']),
                'is_vip' => \api\app\module\VipModule::checkIsVip($row['vip_expire_time']),
            ];
        }

        return $aRet;
    }

    /**
     * 我发表的动态列表--我的帖子管理页面显示
     * @author coase
     */
    public function myDynamicList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;

            $result = Db::name('forum_dynamic')
                ->alias('d')
                ->where('d.user_id', $userId)
                ->where('d.status', 2)
                ->field('d.*')
                ->order('d.create_time', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->error('数据为空');
            }

            $list = [];
            foreach ($result as $row) {
                $is_video = 0;
                if (! empty($row['title'])) {
                    $dynamic_text = $row['title'];
                    if (! empty($row['dynamic_video'])) {
                        $is_video = 1;
                    }
                } elseif (! empty($row['content'])) {
                    $dynamic_text = $row['content'];
                    if (! empty($row['dynamic_video'])) {
                        $is_video = 1;
                    }
                } elseif (! empty($row['picture'])) {
                    $dynamic_text = '[图片]';
                } elseif (! empty($row['video'])) {
                    $dynamic_text = '[视频]';
                    $is_video = 1;
                } else {
                    $dynamic_text = '[未知]';
                }

                $list[] = [
                    'dynamic_id' => $row['id'],
                    'publish_time' => ForumModule::timeTran($row['create_time']),
                    'dynamic_is_video' => $is_video,
                    'dynamic_text' => $dynamic_text,
                ];
            }

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 我发表的回复列表--我的帖子管理页面显示
     * @author coase
     */
    public function myReplyList()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'page' => 'require|min:1|max:1000'
            ]);

            $validate->message([
                'page.require' => '请输入页码!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $iPage = $param['page'];
            $iPageSize = 15;

            $userRow = Db::name('user')->field('id,user_nickname,avatar')->find($userId);

            $result = Db::name('forum_opt_record')
                ->alias('r')
                ->join('forum_reply p', 'p.id = r.reply_id')
                ->join('forum_dynamic d', 'd.id = r.object_id')
                ->where('r.action', 'REPLY')
                ->where('r.user_id', $userId)
                ->where('p.status', 2)
                ->field('p.*,d.id dynamic_id,r.parent_reply_id,r.reply_floor,d.title dynamic_title,d.content dynamic_content,d.picture dynamic_picture,d.video dynamic_video')
                ->order('p.create_time', 'desc')
                ->page($iPage, $iPageSize)
                ->select();
            if (! $result) {
                $this->error('数据为空');
            }

            $list = [];
            foreach ($result as $row) {
                $is_video = 0;
                if (! empty($row['dynamic_title'])) {
                    $dynamic_text = $row['dynamic_title'];
                    if (! empty($row['dynamic_video'])) {
                        $is_video = 1;
                    }
                } elseif (! empty($row['dynamic_content'])) {
                    $dynamic_text = $row['dynamic_content'];
                    if (! empty($row['dynamic_video'])) {
                        $is_video = 1;
                    }
                } elseif (! empty($row['dynamic_picture'])) {
                    $dynamic_text = '[图片]';
                } elseif (! empty($row['dynamic_video'])) {
                    $dynamic_text = '[视频]';
                    $is_video = 1;
                } else {
                    $dynamic_text = '[未知]';
                }

                $list[] = [
                    'user_nickname' => $userRow['user_nickname'],
                    'avatar' => $userRow['avatar'],
                    'reply_id' => $row['id'],
                    'parent_reply_id' => $row['parent_reply_id'],
                    'reply_floor' => $row['reply_floor'],
                    'content' => $row['content'],
                    'publish_time' => ForumModule::timeTran($row['create_time']),
                    'dynamic_id' => $row['dynamic_id'],
                    'dynamic_is_video' => $is_video,
                    'dynamic_text' => $dynamic_text,
                ];
            }

            $this->success("OK", [
                'list' => $list,
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 删除我发表的动态--我的帖子管理页面显示
     * @author coase
     */
    public function myDynamicDelete()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'dynamic_id' => 'require|integer'
            ]);

            $validate->message([
                'dynamic_id.require' => '请输入id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $result = Db::name('forum_dynamic')
                ->where('id', $param['dynamic_id'])
                ->where('user_id', $userId)
                ->count();
            if (! $result) {
                $this->error('数据为空');
            }

            Db::name('forum_dynamic')->where('id', $param['dynamic_id'])->update(['status' => 0, 'delete_time' => time()]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 删除我发表的回复--我的帖子管理页面显示
     * @author coase
     */
    public function myReplyDelete()
    {
        // 用户必须登录
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'reply_id' => 'require|integer'
            ]);

            $validate->message([
                'reply_id.require' => '请输入id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $result = Db::name('forum_reply')
                ->where('id', $param['reply_id'])
                ->where('user_id', $userId)
                ->count();
            if (! $result) {
                $this->error('数据为空');
            }

            Db::name('forum_reply')->where('id', $param['reply_id'])->update(['status' => 0, 'delete_time' => time()]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
