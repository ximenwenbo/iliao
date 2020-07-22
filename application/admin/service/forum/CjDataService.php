<?php
namespace app\admin\service\forum;

use app\admin\service\BaseService;
use think\Db;
use think\Log;
use think\Config;
use think\Exception;
use app\admin\service\MaterialService;
use app\admin\service\file\UploadService;

class CjDataService extends BaseService
{
    /**
     * 同步用户数据
     *
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function translateAccountData($accountUUID)
    {
        $host = [
            "avatar_host" => "https://avatar01.jiaoliuqu.com/",
            "album_host" => "https://avatar01.jiaoliuqu.com/",
            "forum_host" => "https://forumimg01.jiaoliuqu.com/",
            "video_host" => "https://mv01.jiaoliuqu.com/",
            "audio_host" => "https://vox01.jiaoliuqu.com/",
        ];

        $time = time();
        $taquConn = self::connectCjtaqu();

        // 获取用户数据
        $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $accountUUID)->find();
        if ($accountFind['cj_status'] == 0) {
            self::exceptionError('该用户数据还没有被采用');
            return true;
        }
        if ($accountFind['cj_status'] != 1 || ! empty($accountFind['xchat_user_id'])) {
            self::exceptionError('该用户已经同步过了,或者数据有错误');
            return true;
        }
        if (empty($accountFind['nickname']) || empty($accountFind['avatar']) || empty($accountFind['sex_type'])) {
            self::exceptionError('采集的用户资料不全', 1001);
            return false;
        }
        if (! in_array($accountFind['sex_type'], [1,2])) {
            self::exceptionError('用户性别有误', 1002);
            return false;
        }

        // 获取相册
        $aAlbum = [];
        $imgList = json_decode($accountFind['img_list'], true);
        if (! empty($imgList)) {
            foreach ($imgList as $img) {
                if (! empty($img['pic_url'])) {
                    $aAlbum[] = [
                        'down_url' => $host['album_host'] . self::dropDomain($img['pic_url']),
                        'object' => self::getObject($img['pic_url'])
                    ];
                }
                if (count($aAlbum) == 9) {
                    break;
                }
            }
        }

        $userParam = [
            'account_uuid'  => $accountFind['account_uuid'], // 他趣用户uuid
            'user_type'     => 3, // 3:机器人
            'user_nickname' => $accountFind['nickname'],
            'sex'           => $accountFind['sex_type'], // 性别 1:男 2:女
            'age'           => $accountFind['age'],
            'city_name'     => $accountFind['city'],
            'y_level'       => $accountFind['xchat_y_level'],
            'create_time'   => $time,
            'avatar' => [
                'down_url' => !empty(self::dropDomain($accountFind['avatar'])) ? $host['avatar_host'] . self::dropDomain($accountFind['avatar']) : '',
                'object' => self::getObject($accountFind['avatar'])
            ],
            'signature' => $accountFind['personal_profile'],
            'speech_introduction' => [
                'down_url' => !empty(self::dropDomain($accountFind['audio_intro'])) ? $host['audio_host'] . self::dropDomain($accountFind['audio_intro']) : '',
                'object' => self::getObject($accountFind['audio_intro'])
            ],
            'album' => $aAlbum,
            'video' => [
                'down_url' => !empty(self::dropDomain($accountFind['video_intro'])) ? $host['video_host'] . self::dropDomain($accountFind['video_intro']) : '',
                'object' => self::getObject($accountFind['video_intro'])
            ],
        ];

        // 新增机器人用户
        $album = !empty($userParam['album']) ? array_column($userParam['album'], 'object') : [];

        if (! empty($userParam['age'])) {
            $age = $userParam['age'];
        } else {
            $age = $userParam['sex']==1 ? mt_rand(22, 48) : mt_rand(18, 38);
        }

        $addUser = [
            'user_type' => $userParam['user_type'],
            'sex' => $userParam['sex'],
            'age' => $age,
            'create_time' => $time,
            'user_nickname' => $userParam['user_nickname'],
            'avatar' => $userParam['avatar']['object'],
            'signature' => $userParam['signature'],
            'city_name' => $userParam['city_name'],
            'speech_introduction' => $userParam['speech_introduction']['object'],
            'album' => !empty($album) ? json_encode($album, JSON_UNESCAPED_SLASHES) : '',
            'video' => !empty($userParam['video']['object']) ? json_encode([$userParam['video']['object']]) : '',
            'daren_status' => 0,
            'y_level' => $userParam['y_level'],
        ];

        // 随机设置一个经纬度
        if ($userParam['city_name']) {
            $posRes = self::getVicinityPos($userParam['city_name']);
            if ($posRes != false) {
                $posid = array_rand($posRes['results'], 1);
                $addUser['longitude'] = $posRes['results'][$posid]['location']['lng'];
                $addUser['latitude'] = $posRes['results'][$posid]['location']['lat'];
            }
            unset($posRes);
        }

        // 启动事务
        Db::startTrans();
        try {
            $userId = Db::table('t_user')->insertGetId($addUser);

            // 初始化收费设置
            $costArr = [35=>35, 40=>40, 50=>50, 55=>55, 60=>60, 65=>65, 70=>70];
            $video_cost = array_rand($costArr);
            $addSetting = [
                'user_id' => $userId,
                'open_video' => 1,
                'open_speech' => 1,
                'video_cost' => $video_cost,
                'speech_cost' => $video_cost - 5,
            ];
            Db::table('t_user_setting')->insert($addSetting);

            // 分配默认客服
            Db::table('t_allot_robot')->insert([
                'custom_id' => Config::get('option.super_custom_uid'), // 默认客服uid
                'robot_id' => $userId,
                'create_time' => $time,
                'remark' => '采集入库时的默认配置'
            ]);

            // 下载资源
            // 头像
            $avatarRet = self::createMetarial($userParam['avatar']['down_url'], $userParam['avatar']['object'], $userId, 6, 'image');
            if ($avatarRet === false) {
                self::exceptionError("用户{$userId} 上传头像失败", 1003);
                return false;
            }
            // 语音介绍
            if (!empty($userParam['speech_introduction'])) {
                $audioRet = self::createMetarial($userParam['speech_introduction']['down_url'], $userParam['speech_introduction']['object'], $userId, 3, 'audio');
                if ($audioRet === false) {
                    self::exceptionError("用户{$userId} 上传语音介绍失败", 1003);
                    return false;
                }
            }
            // 相册
            if (!empty($userParam['album'])) {
                foreach ($userParam['album'] as $album) {
                    $albumRet = self::createMetarial($album['down_url'], $album['object'], $userId, 1, 'image');
                    if ($albumRet === false) {
                        self::exceptionError("用户{$userId} 上传相册失败", 1003);
                        return false;
                    }
                }
            }
            // 视频
            if (!empty($userParam['video'])) {
                $videoRet = self::createMetarial($userParam['video']['down_url'], $userParam['video']['object'], $userId, 2, 'video');
                if ($videoRet === false) {
                    self::exceptionError("用户{$userId} 上传视频失败", 1003);
                    return false;
                }
            }

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();

            // 修改他趣数据处理失败
            $taquConn->table('t_jiaoliuqu_account')
                ->where('account_uuid', $userParam['account_uuid'])
                ->update([
                    'cj_status' => 10,
                    'trans_time' => $time,
                    'trans_error' => $e->getMessage()
                ]);
            self::exceptionError("用户{$userParam['account_uuid']} 数据同步失败：" . $e->getMessage());
            return false;
        }

        // 修改他趣数据处理成功
        $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $userParam['account_uuid'])->update([
            'cj_status' => 5,
            'trans_time' => $time,
            'xchat_user_id' => $userId,
        ]);

        return true;
    }

    /**
     * 重新更新同步的用户数据
     *
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function repeatUpdateTranslateAccountData($accountUUID)
    {
        $host = [
            "avatar_host" => "https://avatar01.jiaoliuqu.com/",
            "album_host" => "https://avatar01.jiaoliuqu.com/",
            "forum_host" => "https://forumimg01.jiaoliuqu.com/",
            "video_host" => "https://mv01.jiaoliuqu.com/",
            "audio_host" => "https://vox01.jiaoliuqu.com/",
        ];

        $time = time();
        $taquConn = self::connectCjtaqu();

        // 获取用户数据
        $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $accountUUID)->find();
        if ($accountFind['cj_status'] != 5 || empty($accountFind['xchat_user_id'])) {
            self::exceptionError('该用户没有同步过,或者数据有错误');
            return true;
        }
        if (empty($accountFind['nickname']) || empty($accountFind['avatar']) || empty($accountFind['sex_type'])) {
            self::exceptionError('采集的用户资料不全', 1001);
            return false;
        }
        if (! in_array($accountFind['sex_type'], [1,2])) {
            self::exceptionError('用户性别有误', 1002);
            return false;
        }

        $userId = $accountFind['xchat_user_id'];

        // 获取相册
        $aAlbum = [];
        $imgList = json_decode($accountFind['img_list'], true);
        if (! empty($imgList)) {
            foreach ($imgList as $img) {
                if (! empty($img['pic_url'])) {
                    $aAlbum[] = [
                        'down_url' => $host['album_host'] . self::dropDomain($img['pic_url']),
                        'object' => self::getObject($img['pic_url'])
                    ];
                }
                if (count($aAlbum) == 9) {
                    break;
                }
            }
        }

        $userParam = [
            'account_uuid'  => $accountFind['account_uuid'], // 他趣用户uuid
            'user_type'     => 3, // 3:机器人
            'user_nickname' => $accountFind['nickname'],
            'sex'           => $accountFind['sex_type'], // 性别 1:男 2:女
            'age'           => $accountFind['age'],
            'city_name'     => $accountFind['city'],
            'y_level'       => $accountFind['xchat_y_level'],
            'avatar' => [
                'down_url' => !empty(self::dropDomain($accountFind['avatar'])) ? $host['avatar_host'] . self::dropDomain($accountFind['avatar']) : '',
                'object' => self::getObject($accountFind['avatar'])
            ],
            'signature' => $accountFind['personal_profile'],
            'speech_introduction' => [
                'down_url' => !empty(self::dropDomain($accountFind['audio_intro'])) ? $host['audio_host'] . self::dropDomain($accountFind['audio_intro']) : '',
                'object' => self::getObject($accountFind['audio_intro'])
            ],
            'album' => $aAlbum,
            'video' => [
                'down_url' => !empty(self::dropDomain($accountFind['video_intro'])) ? $host['video_host'] . self::dropDomain($accountFind['video_intro']) : '',
                'object' => self::getObject($accountFind['video_intro'])
            ],
        ];

        // 新增机器人用户
        $album = !empty($userParam['album']) ? array_column($userParam['album'], 'object') : [];

        if (! empty($userParam['age'])) {
            $age = $userParam['age'];
        } else {
            $age = $userParam['sex']==1 ? mt_rand(22, 48) : mt_rand(18, 38);
        }

        $updUser = [
            'user_type' => $userParam['user_type'],
            'sex' => $userParam['sex'],
            'age' => $age,
            'user_nickname' => $userParam['user_nickname'],
            'avatar' => $userParam['avatar']['object'],
            'signature' => $userParam['signature'],
            'city_name' => $userParam['city_name'],
            'speech_introduction' => $userParam['speech_introduction']['object'],
            'album' => !empty($album) ? json_encode($album, JSON_UNESCAPED_SLASHES) : '',
            'video' => !empty($userParam['video']['object']) ? json_encode([$userParam['video']['object']]) : '',
            'y_level' => $userParam['y_level'],
        ];

        // 随机设置一个经纬度
        if ($userParam['city_name']) {
            $posRes = self::getVicinityPos($userParam['city_name']);
            if ($posRes != false) {
                $posid = array_rand($posRes['results'], 1);
                $updUser['longitude'] = $posRes['results'][$posid]['location']['lng'];
                $updUser['latitude'] = $posRes['results'][$posid]['location']['lat'];
            }
            unset($posRes);
        }

        // 启动事务
        Db::startTrans();
        try {
            // 下载资源
            // 头像
            $avatarRet = self::createMetarial($userParam['avatar']['down_url'], $userParam['avatar']['object'], $userId, 6, 'image');
            if ($avatarRet === false) {
                self::exceptionError("用户{$userId} 上传头像失败", 1003);
                return false;
            }
            // 语音介绍
            if (!empty($userParam['speech_introduction'])) {
                $audioRet = self::createMetarial($userParam['speech_introduction']['down_url'], $userParam['speech_introduction']['object'], $userId, 3, 'audio');
                if ($audioRet === false) {
                    self::exceptionError("用户{$userId} 上传语音介绍失败", 1003);
                    return false;
                }
            }
            // 相册
            if (!empty($userParam['album'])) {
                foreach ($userParam['album'] as $album) {
                    $albumRet = self::createMetarial($album['down_url'], $album['object'], $userId, 1, 'image');
                    if ($albumRet === false) {
                        self::exceptionError("用户{$userId} 上传相册失败", 1003);
                        return false;
                    }
                }
            }
            // 视频
            if (!empty($userParam['video'])) {
                $videoRet = self::createMetarial($userParam['video']['down_url'], $userParam['video']['object'], $userId, 2, 'video');
                if ($videoRet === false) {
                    self::exceptionError("用户{$userId} 上传视频失败", 1003);
                    return false;
                }
            }

            Db::table('t_user')->where('id', $userId)->update($updUser);

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();

            // 修改他趣数据处理失败
            $taquConn->table('t_jiaoliuqu_account')
                ->where('account_uuid', $userParam['account_uuid'])
                ->update([
                    'cj_status' => 10,
                    'trans_time' => $time,
                    'trans_error' => $e->getMessage()
                ]);
            self::exceptionError("用户{$userParam['account_uuid']} 数据《重复更新》同步失败：" . $e->getMessage());
            return false;
        }

        // 修改他趣数据处理成功
        $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $userParam['account_uuid'])->update([
            'trans_time' => $time,
        ]);

        return true;
    }

    /**
     * 同步动态数据
     *   检测该动态发送者是否存在，若不存在，则放弃；
     *   检测该动态发送者是否存在，若不存在，则新增；
     *
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function translatePostData($postUUID)
    {
        $host = [
            "avatar_host" => "https://avatar01.jiaoliuqu.com/",
            "album_host" => "https://avatar01.jiaoliuqu.com/",
            "forum_host" => "https://forumimg01.jiaoliuqu.com/",
            "video_host" => "https://mv01.jiaoliuqu.com/",
            "audio_host" => "https://vox01.jiaoliuqu.com/",
        ];

        $time = time();
        $taquConn = self::connectCjtaqu();

        // 获取采集的动态数据
        $postFind = $taquConn->table('t_jiaoliuqu_post')->where('uuid', $postUUID)->find();
        if ($postFind['cj_status'] == 0) {
            self::exceptionError('该动态还没有被采用', 2000);
            return false;
        }
        if ($postFind['cj_status'] != 1) {
            self::exceptionError('该动态状态不为1；可能已经同步过了，或者数据不完整...', 2000);
            return false;
        }
        if (empty($postFind['account_uuid']) || (empty($postFind['content']) && empty($postFind['img_list']) && empty($postFind['media_list']))) {
            self::exceptionError('采集的动态数据不全', 2001);
            return false;
        }

        // 获取动态的用户数据
        $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $postFind['account_uuid'])->find();
        if (empty($accountFind)) {
            self::exceptionError('该动态的发布者用户数据没有采集到', 2002);
            return false;
        }
        if (empty($accountFind['xchat_user_id'])) { // 该动态的用户还没有同步到平台
            if (self::translateAccountData($accountFind['account_uuid']) === false) { // 同步采集的用户出错
                return false;
            }

            $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $postFind['account_uuid'])->find();
        }

        $userId = $accountFind['xchat_user_id'];

        // 获取图片
        $aPicture = [];
        $imgList = json_decode($postFind['img_list'], true);
        if (! empty($imgList)) {
            foreach ($imgList as $img) {
                if (! empty($img['pic_url'])) {
                    $aPicture[] = [
                        'down_url' => $host['forum_host'] . self::dropDomain($img['pic_url']),
                        'object' => self::getObject($img['pic_url']),
                        'extra_info' => [
                            'width' => isset($img['width']) ? $img['width'] : 0,
                            'height' => isset($img['height']) ? $img['height'] : 0
                        ]
                    ];
                }
                if (count($aPicture) == 9) {
                    break;
                }
            }
        }

        // 获取视频
        $video = [];
        $mediaList = json_decode($postFind['media_list'], true);
        if (! empty($mediaList['file_name'])) {
            $video = [
                'down_url' => $host['video_host'] . self::dropDomain($mediaList['file_name']),
                'object' => self::getObject($mediaList['file_name']),
                'extra_info' => [
                    'width' => isset($mediaList['width']) ? $mediaList['width'] : 0,
                    'height' => isset($mediaList['height']) ? $mediaList['height'] : 0
                ]
            ];
        }

        $dynamicParam = [
            'post_uuid' => $postFind['uuid'], // 他趣动态uuid
            'user_id'   => $accountFind['xchat_user_id'], // 用户ID
            'type'      => empty($postFind['title']) ? 1 : 2, // 动态类型（1:心情 2:帖子）
            'title'     => empty($postFind['title']) ? $postFind['content'] : $postFind['title'],
            'content'   => empty($postFind['title']) ? '' : $postFind['content'],
            'city_name' => $postFind['city'],
            'circle_id' => $postFind['circle_id'],
            'wet_count' => $postFind['wet_count'],
            'picture'   => $aPicture,
            'video'     => $video,
            'y_level'   => $postFind['xchat_y_level'],
        ];

        $picture = !empty($dynamicParam['picture']) ? array_column($dynamicParam['picture'], 'object') : [];

        // 检测内容是否已经同步过了
        $dynamicIdExist = Db::name('forum_dynamic')->where([
            'user_id' => $dynamicParam['user_id'], 'title' => $dynamicParam['title'], 'content' => $dynamicParam['content']
        ])->value('id');
        if ($dynamicIdExist) {
            // 修改他趣数据处理成功
            $taquConn->table('t_jiaoliuqu_post')
                ->where('uuid', $dynamicParam['post_uuid'])
                ->update([
                    'cj_status' => 5, // 同步成功
                    'trans_time' => $time, // 同步时间
                    'xchat_dynamic_id' => $dynamicIdExist
                ]);

            return true;
        }

        // 启动事务
        Db::startTrans();
        try {
            // 下载资源
            // 视频
            if (!empty($dynamicParam['video'])) {
                $videoRet = self::createMetarial($dynamicParam['video']['down_url'], $dynamicParam['video']['object'], $userId, 12, 'video', $dynamicParam['video']['extra_info']);
                if ($videoRet === false) {
                    self::exceptionError("用户{$userId} 上传动态视频失败", 2004);
                    return false;
                }
            }
            // 图片
            if (!empty($dynamicParam['picture'])) {
                foreach ($dynamicParam['picture'] as $pic) {
                    $picRet = self::createMetarial($pic['down_url'], $pic['object'], $userId, 11, 'image', $pic['extra_info']);
                    if ($picRet === false) {
                        self::exceptionError("用户{$userId} 上传动态图片失败", 2005);
                        return false;
                    }
                }
            }

            // 新增动态
            $addDynamic = [
                'user_id' => $dynamicParam['user_id'],
                'type' => $dynamicParam['type'],
                'title' => $dynamicParam['title'],
                'content' => $dynamicParam['content'],
                'city_name' => $dynamicParam['city_name'],
                'circle_id' => $dynamicParam['circle_id'],
                'picture' => !empty($picture) ? implode(',', $picture) : '',
                'video' => !empty($dynamicParam['video']['object']) ? $dynamicParam['video']['object'] : '',
                'like_num' => $dynamicParam['wet_count'],
                'create_time' => $time,
                'y_level' => $dynamicParam['y_level'],
            ];
            $dynamicId = Db::table('t_forum_dynamic')->insertGetId($addDynamic);

            // 新增操作记录
            $addRecord = [
                'user_id' => $userId,
                'action' => 'NEW',
                'object_id' => $dynamicId,
                'object_type' => 'DYNAMIC',
                'object_user_id' => $userId,
                'create_time' => $time
            ];
            Db::table('t_forum_opt_record')->insert($addRecord);

            // 提交事务
            Db::commit();
        } catch (Exception $e) {
            // 处理失败
            // 回滚事务
            Db::rollback();
            $taquConn->table('t_jiaoliuqu_post')
                ->where('uuid', $dynamicParam['post_uuid'])
                ->update([
                    'cj_status' => 10,
                    'trans_time' => $time,
                    'trans_error' => $e->getMessage()
                ]);
            self::exceptionError("动态{$dynamicParam['post_uuid']} 数据同步失败：" . $e->getMessage(), 2006);
            return false;
        }

        // 修改他趣数据处理成功
        $taquConn->table('t_jiaoliuqu_post')
            ->where('uuid', $dynamicParam['post_uuid'])
            ->update([
                'cj_status' => 5, // 同步成功
                'trans_time' => $time, // 同步时间
                'xchat_dynamic_id' => $dynamicId
            ]);

        return true;
    }

    /**
     * 同步回复数据
     * @param $reviewUUID
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function translateReviewData($reviewUUID)
    {
        $time = time();

        $taquConn = self::connectCjtaqu();

        // 获取采集的回复数据
        $reviewFind = $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->find();
        if (! empty($reviewFind['xchat_reply_id'])) {
            self::exceptionError('该回复已经同步过了', -101);
            return false;
        }
        if (empty($reviewFind['account_uuid']) || empty($reviewFind['post_uuid']) || empty($reviewFind['content'])) {
            $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                'cj_status' => 10,
                'trans_time' => $time,
                'trans_error' => '采集的回复数据不全'
            ]);
            self::exceptionError('采集的回复数据不全');
            return false;
        }

        // 获取回复的用户数据
        $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $reviewFind['account_uuid'])->find();
        if (empty($accountFind)) {
            $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                'cj_status' => 10,
                'trans_time' => $time,
                'trans_error' => '该回复的用户没有采集'
            ]);
            self::exceptionError('该回复的用户没有采集');
            return false;
        }
        if (empty($accountFind['xchat_user_id'])) { // 该回复的用户还没有同步到平台
            if (self::translateAccountData($reviewFind['account_uuid']) === false) { // 同步采集的用户出错
                $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                    'cj_status' => 10,
                    'trans_time' => $time,
                    'trans_error' => self::$errMessage
                ]);
                return false;
            }

            $accountFind = $taquConn->table('t_jiaoliuqu_account')->where('account_uuid', $reviewFind['account_uuid'])->find();
        }

        // 获取回复的动态数据
        $postFind = $taquConn->table('t_jiaoliuqu_post')->where('uuid', $reviewFind['post_uuid'])->find();
        if (empty($postFind)) {
            $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                'cj_status' => 10,
                'trans_time' => $time,
                'trans_error' => '该回复的动态没有采集'
            ]);
            self::exceptionError('该回复的动态没有采集');
            return false;
        }
        if (empty($postFind['xchat_dynamic_id'])) {
            $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                'cj_status' => 10,
                'trans_time' => $time,
                'trans_error' => '该回复的动态还没有同步到平台'
            ]);
            self::exceptionError('该回复的动态还没有同步到平台');
            return false;
        }

        $userId = $accountFind['xchat_user_id'];

        $replyParam = [
            'uuid'      => $reviewFind['uuid'], // 他趣回复uuid
            'post_uuid' => $reviewFind['post_uuid'], // 他趣回复uuid
            'user_id'   => $userId, //用户ID
            'content'   => empty($reviewFind['content']) ? '' : $reviewFind['content'],
            'wet_count' => $reviewFind['wet_count']
        ];

        $currMaxFloor = Db::name('forum_opt_record')
            ->where(['action' => 'REPLY', 'object_id' => $postFind['xchat_dynamic_id'], 'parent_reply_id' => 0])
            ->max('reply_floor');

        try {
            # 新增回复
            $addReply = [
                'user_id' => $userId,
                'content' => $replyParam['content'],
                'reviewed_user_id' => !empty($replyParam['reviewed_user_id']) ? $replyParam['reviewed_user_id'] : '',
                'like_num'  => $replyParam['wet_count'],
                'status'  => 2,
                'create_time' => $time,
            ];
            $replyId = Db::name('forum_reply')->insertGetId($addReply);

            # 新增操作记录
            $addRecord = [
                'user_id' => $userId,
                'action' => 'REPLY',
                'object_id' => $postFind['xchat_dynamic_id'],
                'object_type' => 'DYNAMIC',
                'object_user_id' => Db::name('forum_dynamic')->where('id', $postFind['xchat_dynamic_id'])->value('user_id'),
                'reply_id' => $replyId,
                'reply_floor' => isset($currMaxFloor) ? $currMaxFloor+1 : 0,
                'create_time' => $time
            ];
            Db::name('forum_opt_record')->insertGetId($addRecord);

        } catch (Exception $e) {
            // 处理失败
            $taquConn->table('t_jiaoliuqu_review')->where('uuid', $reviewUUID)->update([
                'cj_status' => 10,
                'trans_time' => $time,
                'trans_error' => $e->getMessage()
            ]);
            self::exceptionError("回复{$replyParam['uuid']} 数据同步失败：" . $e->getMessage());
            return false;
        }

        // 修改他趣数据处理成功
        $taquConn->table('t_jiaoliuqu_review')->where('uuid', $replyParam['uuid'])->update([
            'cj_status' => 5,
            'trans_time' => $time,
            'xchat_reply_id' => $replyId
        ]);

        $taquConn->table('t_jiaoliuqu_post')->where('uuid', $replyParam['post_uuid'])->update([
            'cj_review_untrans_num' => Db::raw('cj_review_untrans_num-1'),
            'cj_review_trans_num' => Db::raw('cj_review_trans_num+1')
        ]);

        return true;
    }

    /**
     * 创建资源文件
     *   下载并上传到阿里云OSS
     * @param $url
     * @param $object
     * @param $userId
     * @param $classId
     * @param $mimeType
     * @return bool|int|string
     * @throws Exception
     */
    private static function createMetarial($url, $object, $userId, $classId, $mimeType, $extraInfo = [])
    {
        if (empty($url)) {
            return '';
        }

        # 获取配置参数
        $aliyunConfig = cmf_get_option('aliyun_oss');
        $bucket = $aliyunConfig['bucket'];

        // 下载文件
        $fileResult = file_get_contents($url);

        // 上传到阿里OSS
        $ossRet = UploadService::uploadObject($fileResult, $object);

        if ($ossRet === true) { // 上传成功
            if (Db::table('t_oss_material')->where('user_id', $userId)->where('object', $object)->count()) {
                $update = [
                    'class_id' => $classId,
                    'bucket' => $bucket,
                    'object' => $object,
//                    'etag' => trim($ossRet['etag'], '"'),
//                    'size' => $ossRet['info']['size_upload'],
                    'mime_type' => $mimeType,
                    'extra_info' => json_encode($extraInfo),
                    'update_time' => time()
                ];

                return Db::table('t_oss_material')->where('user_id', $userId)->where('object', $object)->update($update);
            } else {
                // 保存到数据库
                $input = [
                    'user_id' => $userId,
                    'class_id' => $classId,
                    'bucket' => $bucket,
                    'object' => $object,
//                    'etag' => trim($ossRet['etag'], '"'),
//                    'size' => $ossRet['info']['size_upload'],
                    'mime_type' => $mimeType,
                    'extra_info' => json_encode($extraInfo),
                    'like_num' => mt_rand(50, 500),
                    'look_num' => mt_rand(1000, 9999),
                    'status' => 2,
                    'create_time' => time()
                ];

                return Db::table('t_oss_material')->insert($input);
            }
        }

        return false;
    }

    /**
     * 获取oss object
     * @param $fileStr
     * @return string
     */
    private static function getObject($fileStr)
    {
        $fileStr = self::dropDomain($fileStr);
        if (empty($fileStr)) {
            return '';
        }
        $unistr = md5($fileStr);
        $ext = substr(strrchr($fileStr,"."),1);
        return  sprintf('upload/cj/%s/%s.%s', date('Ymd'), $unistr, $ext);
    }

    /**
     * 去掉域名
     * @param $str
     * @return null|string|string[]
     */
    private static function dropDomain($str)
    {
        return preg_replace("/^https:\/\/[a-z,0-9\.]+\//", '', $str);
    }

    /**
     * 根据城市名称随机获取一个坐标
     * @param $city
     * @return bool|mixed
     * @throws Exception
     */
    public static function getVicinityPos($city)
    {
        $position = cmf_get_option('position');
        $url = 'http://api.map.baidu.com/place/v2/search';
        $aParam = [
            'ak' => $position['baidu_web_key'], // 请求服务权限标识 (测试可使用：hjzlRMegkSsXd4F8iQfdpXKiaHA4SodE)
            'query' => '小区', // 查询关键字 不同关键字间以$符号分隔
            'tag' => '住宅区', // 检索分类偏好，与q组合进行检索，多个分类以","分隔
            'region' => $city,
            'output' => 'json',
            'coord_type' => 'gcj02ll',
            'ret_coordtype' => 'gcj02ll',
            'page_size' => 20, // 单次召回POI数量，默认为10条记录，最大返回20条。多关键字检索时，返回的记录数为关键字个数*page_size。
            'page_num' => mt_rand(0, 10) // 分页页码，默认为0,0代表第一页，1代表第二页，以此类推
        ];
        $url = $url . '?' . http_build_query($aParam);

        try {
            $result = file_get_contents($url);
            $aResult = json_decode($result, true);
            if (!isset($aResult['status']) || $aResult['status'] !== 0) {
                Log::write(sprintf('%s：调用百度地图获取附近位置失败：%s', __METHOD__, var_export($result, true)), 'error');
                return false;
            }

            return $aResult;
        } catch (Exception $e) {
            Log::write(sprintf('%s：调用百度地图获取附近位置系统异常：%s', __METHOD__, $e->getMessage()), 'error');
            return false;
        }
    }

    /**
     * 连接采集他趣数据库
     * @return \think\db\Connection
     * @throws Exception
     */
    public static function connectCjtaqu()
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