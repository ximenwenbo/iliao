<?php
/**
 * 分配机器人给直播间任务
 * User: coase
 * Date: 2019/8/29
 */
namespace app\admin\job;

use function Couchbase\defaultDecoder;
use think\Db;
use think\Log;
use think\Exception;
use think\queue\Job;

class RobotInoutLive
{

    public function fire(Job $job, $data)
    {
        try {
            //这里执行具体的任务
            $liveHome = Db::name('live_home')->where('id', $data['live_id'])->find();

            $userSelect = Db::name('user')
                ->alias('u')
                ->where('user_type', 3)
                ->whereNotIn('id', function ($query){
                    $query->name('live_home_viewer')->where('user_type', 3)->field('user_id');
                })
                ->orderRaw('rand()')
                ->limit(10)
                ->field('id,user_nickname')
                ->select();


            foreach ($userSelect as $item) {
                $insertParams[] = [
                    'user_id' => $item['id'],
                    'live_id' => $data['live_id'],
                    'live_user_id' => $data['user_id'],
                    'user_type' => 3,
                    'status' => 0
                ];
            }
            Db::name('live_home_viewer')->insertAll($insertParams);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误，分配机器人给直播间[任务执行异常]：%s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
        }

        if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
            Log::write(sprintf('%s：任务执行异常，重试3次以上后结束，data：%s', __METHOD__, var_export($data, true)),'error');
            // 也可以重新发布这个任务
            $job->release(3600); //$delay为延迟时间
            exit;
        }


        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
        exit;
    }

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
        Log::write(sprintf('%s：任务执行异常，任务达到最大重试次数后，失败了，data：%s', __METHOD__, var_export($data, true)),'error');

    }

}
