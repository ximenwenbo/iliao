<?php
namespace cjdata\cjtaqu;

use think\Db;
use think\Log;
use think\Exception;
/**
 * 采集他趣数据基础类
 */
class GeneralApi
{
    /**
     * 获取一个用户的全部动态和回复数据
     * @param $accountUuid
     * @return array|bool
     */
    public function getOneUserAllDataByUuid($accountUuid)
    {
        set_time_limit(0);
        ini_set('memory_limit', '400M');
        $accountUuid = strtolower($accountUuid);

        $oUser = new \cjdata\cjtaqu\User;
        $createAccountRes = $oUser->createAccountInfo($accountUuid);

        if ($createAccountRes) {
            $oPost = new \cjdata\cjtaqu\Post;
            $oPost->createPostsByAccountUuid($accountUuid);
        } else {
            return ['code' => 'error', 'msg' => '用户数据获取错误', 'data' => []];
        }

        return ['code' => 'ok', 'msg' => '', 'data' => [
            'new_post' => $oPost->newNum,
            'repeat_post' => $oPost->repeatNum,
        ]];
    }
}