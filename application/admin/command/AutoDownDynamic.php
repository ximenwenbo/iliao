<?php
/**
 * 自动下载他趣动态脚本
 */
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
use think\Exception;
use app\admin\service\forum\AutoDownCjDataService;

class AutoDownDynamic extends Command
{
    protected function configure()
    {
        $this->setName('autodowndynamic')->setDescription('This is auto down taqu dynamic and reply job');
    }

    protected function execute(Input $input, Output $output)
    {
        /** 脚本需要执行的逻辑 **/

        $success = 0;
        $fail = 0;

        try {
            do {
                $downRes = AutoDownCjDataService::downUserAllPosts();
                if ($downRes === false) {
                    Log::write(sprintf('%s：自动下载用户动态失败：%s', __METHOD__, AutoDownCjDataService::$errMessage), 'error');
                }

                $success += $downRes['success_num'];
                $fail += $downRes['fail_num'];

                $output->writeln( date('Y-m-d H:i:s') . ' <autodowndynamic> ' . " 截止目前已经下载成功动态：{$success}条，失败：{$fail}条。");

                sleep(200);
            } while ($downRes === false);

        } catch (Exception $e) {
            $output->writeln( date('Y-m-d H:i:s') . ' <autodowndynamic> ' . ' had be finished!' . ';Error:' . $e->getMessage());
        }

        $output->writeln( date('Y-m-d H:i:s') . ' <autodowndynamic> ' . " had be finished!" . " 本次执行总共下载成功动态：{$success}条，失败：{$fail}条。");
    }

}