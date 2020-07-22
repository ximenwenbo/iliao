<?php
/**
 * User: coase
 * Date: 2019-02-19
 */
namespace api\collect\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Config;
use think\Validate;
use think\Exception;
use api\collect\module\UserModule;

/**
 * #####他趣用户数据的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.
 * 2.
 * ``````````````````
 */
class TaquController extends RestBaseController
{
    /**
     * 接收用户数据-个人主页接口getHomepageInfo
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/i/v1/Info/getHomepageInfo?dress_version=1&uuid=aant9flvcrb1&ticket_id=792314faf9cedd3d9c2e42bc8d96732f&version=1&distinctRequestId=5c0e8374c333f678a465f0ca9c47aafa
     */
    public function initUserinfo4getHomepageInfo()
    {
        try {
            Log::write(sprintf('%s，采集他趣用户数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $validate = new Validate([
                'account_uuid' => 'require', // 用户唯一编码
            ]);

            $validate->message([
                'account_uuid.require' => '请输入account_uuid',
            ]);

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            $userinfoFind = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_uuid'])->find();

            if ($userinfoFind) {
                // 存在，更新
                $updUserinfo['cj_update_time'] = date('Y-m-d H:i:s'); // 采集更新时间
                !empty($param['account_card_id']) && $updUserinfo['account_card_id'] = $param['account_card_id'];
                !empty($param['nickname']) && $updUserinfo['nickname'] = $param['nickname'];
                !empty($param['avatar']) && $updUserinfo['avatar'] = $param['avatar'];
                !empty($param['sex_type']) && $updUserinfo['sex_type'] = $param['sex_type'];
                !empty($param['age']) && $updUserinfo['age'] = $param['age'];
                !empty($param['img_list']) && $updUserinfo['img_list'] = json_encode($param['img_list']);

                $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_uuid'])->update($updUserinfo);
            } else {
                // 不存在，新增
                $addUserinfo = [
                    'account_card_id' => !empty($param['account_card_id']) ? $param['account_card_id'] : 0,
                    'account_uuid' => $param['account_uuid'],
                    'nickname' => !empty($param['nickname']) ? $param['nickname'] : '',
                    'avatar' => !empty($param['avatar']) ? $param['avatar'] : '',
                    'sex_type' => !empty($param['sex_type']) ? $param['sex_type'] : 0,
                    'age' => !empty($param['age']) ? $param['age'] : 0,
                    'img_list' => !empty($param['img_list']) ? json_encode($param['img_list']) : '',
                ];
                $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
            }

            $this->success("OK", $result);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收用户数据-1V1列表页接口getCallerList
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Call/getCallerList?ticket_id=15c27e36a418ab7c6286faafc5fea8fb&page=1&version_number=1&distinctRequestId=4c068e791ce051099464ec9e1cef17bb
     */
    public function initUserinfo4getCallerList()
    {
        try {
            Log::write(sprintf('%s，采集他趣用户列表数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $validate = new Validate([
                'list' => 'require',
            ]);

            $validate->message([
                'list.require' => '请输入list',
            ]);

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $list = $param['list'];

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($list as $item) {
                $userinfoFind = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $item['account_uuid'])->find();

                if ($userinfoFind) {
                    // 存在，更新
                    $updUserinfo['cj_update_time'] = date('Y-m-d H:i:s');
                    !empty($item['nick_name']) && $updUserinfo['nickname'] = $item['nick_name'];
                    !empty($item['avatar']) && $updUserinfo['avatar'] = $item['avatar'];
                    !empty($item['sex_type']) && $updUserinfo['sex_type'] = $item['sex_type'];
                    !empty($item['age']) && $updUserinfo['age'] = $item['age'];
                    !empty($item['city']) && $updUserinfo['city'] = $item['city'];
                    !empty($item['personal_profile']) && $updUserinfo['personal_profile'] = $item['personal_profile'];
                    !empty($item['audio_intro']) && $updUserinfo['audio_intro'] = $item['audio_intro'];
                    !empty($item['video_intro']) && $updUserinfo['video_intro'] = $item['video_intro'];

                    $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $item['account_uuid'])->update($updUserinfo);
                } else {
                    // 不存在，新增
                    $addUserinfo = [
                        'account_uuid' => $item['account_uuid'],
                        'nickname' => !empty($item['nick_name']) ? $item['nick_name'] : '',
                        'avatar' => !empty($item['avatar']) ? $item['avatar'] : '',
                        'sex_type' => !empty($item['sex_type']) ? $item['sex_type'] : 0,
                        'age' => !empty($item['age']) ? $item['age'] : 0,
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'personal_profile' => !empty($item['personal_profile']) ? $item['personal_profile'] : '',
                        'audio_intro' => !empty($item['audio_intro']) ? $item['audio_intro'] : '',
                        'video_intro' => !empty($item['video_intro']) ? $item['video_intro'] : '',
                    ];

                    $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
                }
            }

            $this->success("OK", $result);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收动态数据-个人主页动态列表接口getAccountPostList
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Account/getAccountPostList?api_version=2&account_uuid=akdusdoq2uax&page=1&is_video=0&distinctRequestId=b96f8416c0b3fb7c4eec204d168aa23d
     */
    public function initDynamic4getAccountPostList()
    { $this->success("暂时关闭");
        try {
            Log::write(sprintf('%s，采集他趣动态列表数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $validate = new Validate([
                'list' => 'require',
                'account_uuid' => 'require',
            ]);

            $validate->message([
                'list.require' => '请输入list',
            ]);

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $list = $param['list'];

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($list as $item) {
                // 用户不存在时，不入库
                if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_uuid'])->count()) {
                    $addUserinfo = [
                        'account_uuid' => $param['account_uuid'],
                        'nickname' => $item['nickname'],
                        'avatar' => $item['avatar'],
                        'sex_type' => $item['sex_type'],
                    ];
                    $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
                }

                if (!empty($item['title']) && empty($item['content'])) {
                    $item['content'] = $item['title'];
                    $item['title'] = '';
                }

                if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['uuid'])->count()) {
                    // 不存在，新增
                    $addDynamic = [
                        'uuid' => $item['uuid'],
                        'account_uuid' => $param['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['circle_id']) ? $item['circle_id'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : '',
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->insert($addDynamic);
                } else {
                    // 存在，更新
                    $updDynamic = [
                        'account_uuid' => $param['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['circle_id']) ? $item['circle_id'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : '',
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['uuid'])->update($updDynamic);
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收动态数据-根据id获取动态详情接口getPostById
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Post/getPostById?post_uuid=bbjl5zg7csyu7&distinctRequestId=d935cdb935211f7920d7ddc8ff889a4c
     */
    public function initDynamic4getPostById()
    {
        try {
            Log::write(sprintf('%s，采集他趣动态数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!empty($param['title']) && empty($param['content'])) {
                $param['content'] = $param['title'];
                $param['title'] = '';
            }

            if (empty($param['account_uuid'])) {
                $this->success("缺少account_uuid");
            }

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            // 用户不存在时，不入库
            if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_uuid'])->count()) {
                $addUserinfo = [
                    'account_uuid' => $param['account_uuid'],
                    'nickname' => $param['nickname'],
                    'avatar' => $param['avatar'],
                    'sex_type' => $param['sex_type'],
                ];
                $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
            }

            if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $param['uuid'])->count()) {
                // 不存在，新增
                $addDynamic = [
                    'uuid' => $param['uuid'],
                    'account_uuid' => $param['account_uuid'],
                    'title' => !empty($param['title']) ? $param['title'] : '',
                    'content' => !empty($param['content']) ? $param['content'] : '',
                    'img_list' => !empty($param['img_list']) ? json_encode($param['img_list'], true) : '',
                    'media_list' => !empty($param['post_media']) ? json_encode($param['post_media'], true) : '',
                    'city' => !empty($param['city']) ? $param['city'] : '',
                    'circle_id' => !empty($param['cid']) ? $param['cid'] : 0,
                    'wet_count' => !empty($param['wet_count']) ? $param['wet_count'] : 0,
                    'create_time' => !empty($param['create_time']) ? $param['create_time'] : 0,
                    'update_time' => !empty($param['update_time']) ? $param['update_time'] : 0,
                ];

                $cjtaquDbConn->table('t_jiaoliuqu_post')->insert($addDynamic);
            } else {
                // 存在，更新
                $updDynamic = [
                    'account_uuid' => $param['account_uuid'],
                    'title' => !empty($param['title']) ? $param['title'] : '',
                    'content' => !empty($param['content']) ? $param['content'] : '',
                    'img_list' => !empty($param['img_list']) ? json_encode($param['img_list'], true) : '',
                    'media_list' => !empty($param['post_media']) ? json_encode($param['post_media'], true) : '',
                    'city' => !empty($param['city']) ? $param['city'] : '',
                    'circle_id' => !empty($param['cid']) ? $param['cid'] : 0,
                    'wet_count' => !empty($param['wet_count']) ? $param['wet_count'] : 0,
                    'create_time' => !empty($param['create_time']) ? $param['create_time'] : 0,
                    'update_time' => !empty($param['update_time']) ? $param['update_time'] : 0,
                ];

                $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $param['uuid'])->update($updDynamic);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收动态数据-根据id获取视频动态详情接口getVideoPostById
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Post/getVideoPostById?&distinctRequestId=28f681fdf27edac1acdbbde3170a352c
     */
    public function initDynamic4getVideoPostById()
    {
        try {
            Log::write(sprintf('%s，采集他趣视频动态数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $data = $this->request->param('data');
            $list = json_decode($data, true);

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($list as $item) {
                if (empty($item['account_uuid'])) {
                    continue;
                }

                // 用户不存在时，不入库
                if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $item['account_uuid'])->count()) {
                    $addUserinfo = [
                        'account_uuid' => $item['account_uuid'],
                        'nickname' => $item['nickname'],
                        'avatar' => $item['avatar'],
                        'sex_type' => $item['sex_type'],
                    ];
                    $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
                }

                if (!empty($item['title']) && empty($item['content'])) {
                    $item['content'] = $item['title'];
                    $item['title'] = '';
                }

                if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['uuid'])->count()) {
                    // 不存在，新增
                    $addDynamic = [
                        'uuid' => $item['uuid'],
                        'account_uuid' => $item['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['cid']) ? $item['cid'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : 0,
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->insert($addDynamic);
                } else {
                    // 不存在，新增
                    $updDynamic = [
                        'account_uuid' => $item['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['cid']) ? $item['cid'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : 0,
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['uuid'])->update($updDynamic);
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收动态回复数据-个人主页动态列表接口getReviewsWithReply
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Review/getReviewsWithReply?post_uuid=bbjefvbkyh5ia&page=1&is_poster=0&sort=asc&distinctRequestId=90aa9e342cc12884dc115687a7a894db
     */
    public function initDynamicReply4getReviewsWithReply()
    {
        try {
            Log::write(sprintf('%s，采集他趣动态回复数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $validate = new Validate([
                'review_list' => 'require',
            ]);

            $validate->message([
                'review_list.require' => '请输入review_list',
            ]);

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $list = $param['review_list'];

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($list as $item) {
                // 用户不存在时
                if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $item['account_uuid'])->count()) {
                    $addUserinfo = [
                        'account_uuid' => $item['account_uuid'],
                        'nickname' => $item['nickname'],
                        'avatar' => $item['avatar'],
                        'sex_type' => $item['sex_type'],
                    ];
                    $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
                }
                if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['post_uuid'])->count()) {
                    $cjtaquDbConn->table('t_jiaoliuqu_post')->insert(['uuid' => $item['post_uuid']]);
                }

                if (! $cjtaquDbConn->table('t_jiaoliuqu_review')->where('uuid', $item['uuid'])->count()) {
                    // 不存在，新增
                    $addReply = [
                        'uuid' => !empty($item['uuid']) ? $item['uuid'] : '',
                        'post_uuid' => !empty($item['post_uuid']) ? $item['post_uuid'] : '',
                        'account_uuid' => $item['account_uuid'],
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : '',
                        'cj_status' => 1, // todo 默认都采用
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : '',
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_review')->insert($addReply);

                    // 更新动态的未同步回复数量
                    $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['post_uuid'])->setInc('cj_review_untrans_num', 1);
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收动态列表数据-社区动态推荐列表接口getBestPostList
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Home/getBestPostList?circle_type=1&page=1&live_count=0&version_number=2&distinctRequestId=df565421148ad8d8d195894933596cb5
     */
    public function initDynamic4getBestPostList()
    { $this->success("暂时关闭");
        try {
            Log::write(sprintf('%s，采集他趣推荐动态列表数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $validate = new Validate([
                'post_list' => 'require',
            ]);

            $validate->message([
                'post_list.require' => '请输入post_list',
            ]);

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $list = $param['post_list'];

            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($list as $item) {
                if (empty($item['account_uuid'])) {
                    continue;
                }
                // 用户不存在时，不入库
                if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $item['account_uuid'])->count()) {
                    $addUserinfo = [
                        'account_uuid' => $item['account_uuid'],
                        'nickname' => $item['nickname'],
                        'avatar' => $item['avatar'],
                        'sex_type' => $item['sex_type'],
                    ];
                    $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUserinfo);
                }

                if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['post_uuid'])->count()) {
                    // 不存在，新增
                    $addDynamic = [
                        'uuid' => $item['post_uuid'],
                        'account_uuid' => $item['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['circle_id']) ? $item['circle_id'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : 0,
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->insert($addDynamic);
                } else {
                    // 存在，更新
                    $updDynamic = [
                        'account_uuid' => $item['account_uuid'],
                        'title' => !empty($item['title']) ? $item['title'] : '',
                        'content' => !empty($item['content']) ? $item['content'] : '',
                        'img_list' => !empty($item['img_list']) ? json_encode($item['img_list'], true) : '',
                        'media_list' => !empty($item['post_media']) ? json_encode($item['post_media'], true) : '',
                        'city' => !empty($item['city']) ? $item['city'] : '',
                        'circle_id' => !empty($item['circle_id']) ? $item['circle_id'] : 0,
                        'wet_count' => !empty($item['wet_count']) ? $item['wet_count'] : 0,
                        'create_time' => !empty($item['create_time']) ? $item['create_time'] : 0,
                        'update_time' => !empty($item['update_time']) ? $item['update_time'] : 0,
                    ];

                    $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $item['post_uuid'])->update($updDynamic);
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 接收圈子数据-圈子列表接口getAllList
     *
     * 他趣接口示例：
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Circle/getAllList?distinctRequestId=fb903ba19ef31cd05e2d119f9e4c4a1c
     */
    public function initCircle4getAllList()
    {
        try {
            Log::write(sprintf('%s，采集他趣圈子列表数据，接收的数据：%s', __METHOD__, var_export($_REQUEST,true)),'log');

            $data = $this->request->param('data');
            $param = json_decode($data, true);
            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            foreach ($param as $aList) {
                foreach ($aList['categorys'] as $item) {
                    if (!$cjtaquDbConn->table('t_jiaoliuqu_circle')->where('id', $item['id'])->count()) {
                        // 不存在，新增
                        $addCircle = [
                            'id' => $item['id'],
                            'type_id' => $aList['type_id'],
                            'type_name' => $aList['type_name'],
                            'circle_name' => $item['circle_name'],
                            'introduction' => $item['introduction'],
                            'cover' => $item['cover'],
                            'img' => $item['img'],
                        ];
                        $cjtaquDbConn->table('t_jiaoliuqu_circle')->insert($addCircle);
                    } else {
                        // 存在，更新
                        $updCircle = [
                            'type_id' => $aList['type_id'],
                            'type_name' => $aList['type_name'],
                            'circle_name' => $item['circle_name'],
                            'introduction' => $item['introduction'],
                            'cover' => $item['cover'],
                            'img' => $item['img'],
                            'cj_update_time' => date('Y-m-d H:i:s'),
                        ];
                        $cjtaquDbConn->table('t_jiaoliuqu_circle')->where('id', $item['id'])->update($updCircle);
                    }
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 连接采集他趣数据库
     * @return \think\db\Connection
     * @throws Exception
     */
    private function connectCjtaqu()
    {
        try {
            $cjtaquDbConfig = Config::get('option.cjtaqu_db');

            $taquConn = Db::connect($cjtaquDbConfig);
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据库连接失败：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('采集他趣数据库连接失败：' . $e->getMessage());
        }

        return $taquConn;
    }
}
