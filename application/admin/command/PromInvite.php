<?php
/**
 * 推广奖励业务脚本
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use app\admin\service\prom\InviteService;

class PromInvite extends Command
{
    protected function configure()
    {
        $this->setName('prominvitebonus')->setDescription('Batch finished invited bonus !');
    }

    protected function execute(Input $input, Output $output)
    {
        // 脚本需要执行的逻辑
        $res = InviteService::batchAssignBonus();

        $output->writeln( date('Y-m-d H:i:s') . ",推广奖励脚本执行结果：{$res} had be finished!");
    }
}