<?php
namespace app\admin\service\forum;

use app\admin\service\BaseService;
use think\Db;
use think\Log;
use think\Config;
use think\Exception;

class AutoDownCjDataService extends BaseService
{
    /**
     * 下载用户的所有动态
     * @return array|bool
     */
    public static function downUserAllPosts()
    {
        $tableName = 'taqu15';
        $limit = 10;
        $successNum = 0;
        $failNum = 0;

        try {
            $caijiConn = self::connectCaiji();

            $renzhengUuids = $caijiConn->table($tableName)->where('down_status=0 AND shifourenzheng=1')->group('yonghuid')->limit($limit)->column('yonghuid');

            foreach ($renzhengUuids as $accountUuid) {
                $oGeneralApi = new \cjdata\cjtaqu\GeneralApi;
                $res = $oGeneralApi->getOneUserAllDataByUuid($accountUuid);
                if ($res['code'] == 'ok') {
                    $caijiConn->table($tableName)->where('yonghuid', $accountUuid)->update(['down_status' => 3]);

                    $successNum++;
                    Log::write(sprintf('用户uuid:%s，新增动态%d条，重复动态%d条.', $accountUuid, $res['data']['new_post'], $res['data']['repeat_post']), 'log');
                    continue;
                } else {
                    $failNum++;
                    self::exceptionError("用户uuid:{$accountUuid}，同步失败，{$res['msg']}");
                }

                sleep(100);
            }

            return ['success_num' => $successNum, 'fail_num' => $failNum];
        } catch (Exception $e) {
            Log::write(sprintf('%s：用户uuid:%s，自动发布一条新的动态失败：%s IN FILE %s', __METHOD__, $accountUuid, $e->getMessage(), $e->getFile()), 'error');
            self::exceptionError('发布异常：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 连接采集他趣数据库2
     * @return \think\db\Connection
     * @throws Exception
     */
    public static function connectCaiji()
    {
        try {
            $caijiConfig = Config::get('option.caiji');

            $caijiConn = Db::connect($caijiConfig);
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据库2连接失败：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('采集他趣数据库2连接失败：' . $e->getMessage());
        }

        return $caijiConn;
    }

}