<?php
/**
 * 腾讯云回调业务功能模块
 * Class TxyunCallbackModule
 * @package api\app\module\txyun
 */
namespace api\app\module\txyun;
use think\Db;
use think\Log;
use think\Exception;
use dctxyun\Imapi;
use dctxyun\Liveapi;
use api\app\module\BaseModule;

class TxyunCallbackModule extends BaseModule
{
    /**
     * 成员状态变更回调
     * @param $param
     * @return bool
     */
    public static function afterStateChange($param)
    {
        if (empty($param['Info'])) {
            return false;
        }

        $action = !empty($param['Info']['Action']) ? : '';
        $toAccount = !empty($param['Info']['To_Account']) ? : '';

        if ($action == 'Login') {
            // 上线


        } else {
            // 下线

        }

        return true;
    }

    /**
     * 新成员入群之后回调--直播-关闭 todo
     * @param $param
     * @return bool
     * @throws Exception
     */
    public static function afterNewMemberJoin4Live($param)
    {
        if (empty($param['NewMemberList'])) {
            return false;
        }

        $groupId = $param['GroupId'];
        $time = time();
        $num = count($param['NewMemberList']);

        // 启动事务
        Db::startTrans();
        try {
            foreach ($param['NewMemberList'] as $item) {
                $existId = Db::name('live_home_viewer')->where(['user_id'=>$item['Member_Account'],'live_user_id'=>$groupId])->value('id');
                if ($existId) {
                    Db::name('live_home')->where('id', $existId)->update([
                        'status' => 1,
                        'update_time' => $time
                    ]);
                } else {
                    // 直播间观众数据入库
                    Db::name('live_home_viewer')->insertAll([
                        'user_id' => $item['Member_Account'],
                        'live_user_id' => $groupId,
                        'status' => 1,
                        'create_time' => $time
                    ]);
                }
            }

            // 增加直播间的观众数
            Db::name('live_home')->where(['user_id' => $groupId, 'status' => 1])->update([
                'online_viewer' => Db::raw('online_viewer+' . $num),
                'total_viewer' => Db::raw('total_viewer+' . $num),
            ]);

            // 提交事务
            Db::commit();

        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            return false;
        }

        return true;
    }

    /**
     * 群成员离开之后回调--直播-关闭 todo
     *
     * @param $param
     * @return bool
     */
    public static function afterMemberExit4Live($param)
    {
        if (empty($param['ExitMemberList'])) {
            return false;
        }

        $groupId = $param['GroupId'];

        foreach ($param['ExitMemberList'] as $item) {
            $aUid[] = $item['Member_Account'];
        }

        // 启动事务
        Db::startTrans();
        try {
            // 直播间观众数据删除
            Db::name('live_home_viewer')->where('live_user_id', $groupId)->whereIn('user_id', $aUid)->update([
                'status' => 0,
                'update_time' => time()
            ]);

            // 直播间的观众数
            Db::name('live_home')->where(['user_id' => $groupId, 'status' => 1])->setDec('online_viewer', count($param['ExitMemberList']));

            // 提交事务
            Db::commit();

        } catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            return false;
        }

        return true;
    }

    /**
     * 直播断流回调
     * @param array $streamFind
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function zhiboStream_0($streamFind)
    {
        $time = time();

        if ($streamFind['option_class_id'] == 21) { // 主播断流
            // 更新主播状态，活跃
            Db::name('user_token')->where('user_id', $streamFind['user_id'])->update(['online_status'=>1]);

            if (Db::name('live_home')->where('id', $streamFind['option_id'])->value('status') != 1) {
                // 直播间已经正常关闭了
                return true;
            }

            // 确认直播流不存在，再关闭直播间、解散群
            sleep(15);
            if (Liveapi::describeStreamState($streamFind['stream_id']) == 'active') {
                return false;
            }

            // 启动事务
            Db::startTrans();
            try {
                Db::name('live_home')->where('id', $streamFind['option_id'])->update([
                    'status' => 3,
                    'error_msg' => '异常断流关闭',
                    'end_time' => $time
                ]);

                Db::name('live_stream')->where('id', $streamFind['id'])->update([
                    'status' => 2,
                    'end_time' => $time
                ]);

                Db::name('live_home_viewer')->where(['live_id'=>$streamFind['option_id'], 'user_type'=>3])->delete();

                // 提交事务
                Db::commit();

            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::write(sprintf('%s：数据库操作错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
                return false;
            }

            // 销毁直播间群
            Imapi::destroyGroup($streamFind['user_id']);

            $aSlaveStream = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $streamFind['option_id'], 'option_class_id' => 22, 'status' => 1])
                ->column('stream_id');
            if (!empty($aSlaveStream)) {
                // 取消混流
                Liveapi::cancelMixStream($streamFind['stream_id'], $streamFind['mix_stream_session_id']);
            }

        } elseif ($streamFind['option_class_id'] == 22) { // 连麦者断流
            Db::name('live_stream')->where('id', $streamFind['id'])->update([
                'status' => 2,
                'end_time' => $time
            ]);

            // 腾讯云混流
            $masterStreamFind = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $streamFind['option_id'], 'option_class_id' => 21, 'status' => 1])
                ->find();
            if (! $masterStreamFind) { // 如果主播先断流了，那么就不需要再取消混流和重新混流了
                return true;
            }
            $masterStream = $masterStreamFind['stream_id'];
            $mixStreamSessionId = $masterStreamFind['mix_stream_session_id'];

            $aSlaveStream = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $streamFind['option_id'], 'option_class_id' => 22, 'status' => 1])
                ->column('stream_id');

            // 取消混流
            Liveapi::cancelMixStream($masterStream, $mixStreamSessionId);

            if (!empty($aSlaveStream)) {
                // 重新混流
                Liveapi::startMixStream($masterStream, $aSlaveStream, $mixStreamSessionId);
            }

        } else {
            Log::write(sprintf('%s：腾讯云直播回调参数有误，$aStream: %s', __METHOD__, var_export($streamFind,true)),'error');
            return false;
        }

        return true;
    }

    /**
     * 直播推流回调
     * @param array $streamFind
     * @return bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function zhiboStream_1($streamFind)
    {
        $time = time();

        if ($streamFind['option_class_id'] == 21) { // 主播推流
            // 启动事务
            Db::startTrans();
            try {
                Db::name('live_home')->where('id', $streamFind['option_id'])->update([
                    'status' => 1,
                    'start_time' => $time
                ]);

                Db::name('live_stream')->where('id', $streamFind['id'])->update([
                    'status' => 1,
                    'start_time' => $time
                ]);

                // 提交事务
                Db::commit();

            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::write(sprintf('%s：数据库操作错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
                return false;
            }

            // 更新主播状态，直播中
            Db::name('user_token')->where('user_id', $streamFind['user_id'])->update(['online_status'=>3]);

        } elseif ($streamFind['option_class_id'] == 22) { // 连麦者推流
            Db::name('live_stream')->where('id', $streamFind['id'])->update([
                'status' => 1,
                'start_time' => $time
            ]);

            sleep(3); // 应腾讯云要求，新推流后，需要等待几秒后才能混流

            // 腾讯云混流
            $streamSelect = Db::name('live_stream')
                ->where(['option_type' => 2, 'option_id' => $streamFind['option_id'], 'status' => 1])
                ->select();
            foreach ($streamSelect as $item) {
                if ($item['option_class_id'] == 21) {
                    $masterStream = $item['stream_id'];
                    $mixStreamSessionId = $item['mix_stream_session_id'];
                } else {
                    $slaveStreams[] = $item['stream_id'];
                }
            }

            if (!empty($slaveStreams) && !empty($mixStreamSessionId)) {
                // 开始混流
                Liveapi::startMixStream($masterStream, $slaveStreams, $mixStreamSessionId, 4);
            }

        } else {
            Log::write(sprintf('%s：腾讯云直播回调参数有误，$aStream: %s', __METHOD__, var_export($streamFind,true)),'error');
            return false;
        }

        return true;
    }

    /**
     * 直播录制回调
     * @param $streamFind
     * @param $request
     * @return bool
     */
    public static function zhiboStream_100($streamFind, $request)
    {
        if ($streamFind['option_class_id'] == 21) { // 主播推流 录播
            $insData = [
                'option_type' => $streamFind['option_type'],
                'option_id' => $streamFind['option_id'],
                'option_class_id' => $streamFind['option_class_id'],
                'user_id' => $streamFind['user_id'],
                'video_url' => $request['video_url'],
                'format' => isset($request['file_format']) ? $request['file_format'] : '',
                'duration' => isset($request['duration']) ? $request['duration'] : '',
                'size' => isset($request['file_size']) ? $request['file_size'] : '',
                'ext' => json_encode($request),
            ];
            if (! Db::name('live_video')->where('video_url', $request['video_url'])->count()) {
                Db::name('live_video')->insert($insData);
                return true;
            }
        }

        return false;
    }
}