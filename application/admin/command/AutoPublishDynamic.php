<?php
/**
 * 自动发布社区动态脚本
 */
namespace app\admin\command;

use app\admin\service\forum\CjDataService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use app\admin\service\forum\AutoTransCjDataService;

class AutoPublishDynamic extends Command
{
    protected function configure()
    {
        $this->setName('autopublishdynamic')->setDescription('This is auto publish dynamic and reply job');
    }

    protected function execute(Input $input, Output $output)
    {
        /** 脚本需要执行的逻辑 **/

        try {
            // 发布一条动态
            if ($this->publishDynamicFrequenceCrontol()) {
//            do {
                $dynamicRes = AutoTransCjDataService::publishOneDynamic();
                if ($dynamicRes === false) {
                    Log::write(sprintf('%s：自动发布一条新的动态失败：%s', __METHOD__, AutoTransCjDataService::$errMessage), 'error');
                }
//            } while ($dynamicRes === false);
            }

            // 发布多条回复
            if ($this->publishReplyFrequenceCrontol()) {
                AutoTransCjDataService::publishSomeReply();
            }

            // 重新同步有更新的用户数据
            AutoTransCjDataService::repeatUpdateUserinfo();
        } catch (Exception $e) {
            $output->writeln( date('Y-m-d H:i:s') . ' <autopublishdynamic> ' . ' had be finished!' . ';Error:' . $e->getMessage());
        }

        $output->writeln( date('Y-m-d H:i:s') . ' <autopublishdynamic> ' . " had be finished!");
    }

    /**
     * 动态发布频率控制
     * @return bool
     */
    private function publishDynamicFrequenceCrontol()
    {
        if (mt_rand(1, 20) == 1) {
            return true;
        }
        return false;
    }

    /**
     * 回复发布频率控制
     * @return bool
     */
    private function publishReplyFrequenceCrontol()
    {
        if (mt_rand(1, 3) == 1) {
            return true;
        }
        return false;
    }
}