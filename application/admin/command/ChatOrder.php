<?php
/**
 * 聊天业务脚本
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use api\app\module\job\JobChatModule;

class ChatOrder extends Command
{
    protected function configure()
    {
        $this->setName('finisherrorchatorder')->setDescription('Batch finished error chat orders !');
    }

    protected function execute(Input $input, Output $output)
    {
        // 脚本需要执行的逻辑
        $resNum = JobChatModule::chatOrder4Job();

        $output->writeln( date('Y-m-d H:i:s') . ",本次有 {$resNum} 个error_chat_order had be finished!");
    }
}