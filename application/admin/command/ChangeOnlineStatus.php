<?php
/**
 * 聊天业务脚本
 *   修改用户在线状态
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use app\admin\service\commandir\ChangeOnlineStatusService;

class ChangeOnlineStatus extends Command
{
    protected function configure()
    {
        $this->setName('changeuseronlinestatus')->setDescription('Batch change user online status !');
    }

    protected function execute(Input $input, Output $output)
    {
        // 脚本需要执行的逻辑
        ChangeOnlineStatusService::changeStatus();

        $output->writeln( date('Y-m-d H:i:s') . ",change user online status had be finished!");
    }
}