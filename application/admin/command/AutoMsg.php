<?php
/**
 * 自动消息发送业务脚本
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use app\admin\service\msg\SingleMsgService;
use app\admin\service\msg\AutoMsgByRobot2ManService;

class AutoMsg extends Command
{
    protected function configure()
    {
        $this->setName('autosendmsg')->setDescription('This is auto send msg job');
    }

    protected function execute(Input $input, Output $output)
    {
        // 脚本需要执行的逻辑
        SingleMsgService::sendSingleMsg2Man();

        // 随机获取机器人给男用户发送营销类单聊消息
        AutoMsgByRobot2ManService::sendMsg();

        $output->writeln( date('Y-m-d H:i:s') . ' <autosendmsg> ' . " had be finished!");
    }
}