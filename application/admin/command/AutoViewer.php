<?php
/**
 * 自动出入直播间观众业务脚本
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\commandir\AutoViewerService;

class AutoViewer extends Command
{
    protected function configure()
    {
        $this->setName('autoinoutliving')->setDescription('This is auto in or out live home job');
    }

    /**
     * 执行脚本
     * @param Input $input
     * @param Output $output
     * @return bool|int|null
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function execute(Input $input, Output $output)
    {
        // 脚本需要执行的逻辑
        $maxRobotNum1 = 20;
        $maxRobotNum2 = 50;

        // 查找直播中的房间
        $liveSelect = Db::name('live_home')->where('status', 1)->field('id,user_id')->select()->toArray();
        if (empty($liveSelect)) {
            return false;
        }
        sleep(mt_rand(3, 10));
        foreach ($liveSelect as $liveRow) {
            // 直播间当前机器人数量
            $robotViewNum = Db::name('live_home_viewer')->where(['live_id'=>$liveRow['id'], 'user_type'=>3, 'status'=>1])->count();

            if ($robotViewNum < $maxRobotNum1) {
//                sleep(mt_rand(3, 15));
                AutoViewerService::sendRobot($liveRow);

//                sleep(mt_rand(3, 15));
//                AutoViewerService::sendRobot($liveRow);

//                sleep(mt_rand(3, 10));
//                AutoViewerService::sendRobotOut($liveRow);

//                sleep(mt_rand(3, 15));
//                AutoViewerService::sendRobot($liveRow);

//            } elseif ($robotViewNum < $maxRobotNum2) {
//                sleep(mt_rand(3, 12));
//                AutoViewerService::sendRobot($liveRow);
//
//                sleep(mt_rand(3, 12));
//                AutoViewerService::sendRobot($liveRow);
//
//                sleep(mt_rand(3, 10));
//                AutoViewerService::sendRobotOut($liveRow);
//
//                sleep(mt_rand(3, 12));
//                AutoViewerService::sendRobot($liveRow);
//
//                sleep(mt_rand(3, 10));
//                AutoViewerService::sendRobotOut($liveRow);

            } else {
//                sleep(mt_rand(3, 15));
//                AutoViewerService::sendRobot($liveRow);
//
//                sleep(mt_rand(3, 10));
//                AutoViewerService::sendRobotOut($liveRow);

//                sleep(mt_rand(3, 15));
                AutoViewerService::sendRobot($liveRow);

                sleep(mt_rand(2, 3));
                AutoViewerService::sendRobotOut($liveRow);
            }
        }

        $output->writeln( date('Y-m-d H:i:s') . ' <autoinoutliving> ' . " had be finished!");
    }
}