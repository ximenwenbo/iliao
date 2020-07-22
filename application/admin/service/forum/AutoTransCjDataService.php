<?php
namespace app\admin\service\forum;

use app\admin\service\BaseService;
use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\forum\CjDataService;

class AutoTransCjDataService extends BaseService
{
    /**
     * 发布一条新的动态（随机获取一条未同步的动态同步到平台）
     * @return bool
     * @throws \Exception
     */
    public static function publishOneDynamic()
    {
        try {
            $taquConn = CjDataService::connectCjtaqu();

            // 取出12小时内被同步动态的用户
            $existAccountUuid = $taquConn->table('t_jiaoliuqu_post')->where('cj_status=5 AND trans_time>'.strtotime('- 24 hours'))->column('account_uuid');
            $existAccountUuid = array_unique($existAccountUuid);
            if (! empty($existAccountUuid)) {
                $accountUuids = array_map(function($uuid){return "'".$uuid."'";}, $existAccountUuid);
                $accountUuids = implode(',', $accountUuids);
                // 取出一条未同步的采用动态
                $postRows = $taquConn->query("select * from t_jiaoliuqu_post where cj_status=1 AND account_uuid NOT IN ({$accountUuids}) order by rand() limit 1");
            } else {
                // 取出一条未同步的采用动态
                $postRows = $taquConn->query('select * from t_jiaoliuqu_post where cj_status=1 order by rand() limit 1');
            }

            if (empty($postRows[0])) { // 未找到需要同步的动态
                return true;
            }
            $postRow = $postRows[0];

            $transPostRet = CjDataService::translatePostData($postRow['uuid']);
            if ($transPostRet === false) { // 同步失败
                // 修改源数据状态
                $taquConn->table('t_jiaoliuqu_post')
                    ->where('uuid', $postRow['uuid'])
                    ->update([
                        'cj_status' => 10,
                        'trans_time' => time(),
                        'trans_error' => CjDataService::$errMessage
                    ]);
                self::exceptionError('同步失败,' . CjDataService::$errMessage);
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::write(sprintf('%s：自动发布一条新的动态失败：%s IN FILE %s', __METHOD__, $e->getMessage(), $e->getFile()), 'error');
            self::exceptionError('发布异常：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 发布多条回复（随机获取存在未同步回复的动态，给每条动态各同步一条回复到平台）
     * @return bool
     */
    public static function publishSomeReply()
    {
        try {
            $taquConn = CjDataService::connectCjtaqu();

            // 取出已经同步的且存在未同步回复的有效动态
            $postUUIDColumn = $taquConn->table('t_jiaoliuqu_post')->alias('p')
                ->join('t_jiaoliuqu_review r', "r.post_uuid=p.uuid AND r.cj_status=1")
                ->where('p.cj_status', 5)
                ->where('p.cj_review_untrans_num > 0')
                ->order('p.cj_create_time')
                ->limit(100)
                ->column('p.uuid');
            if (! $postUUIDColumn) { // 未找到需要同步回复的动态
                return true;
            }
            $postUUIDColumn = array_flip($postUUIDColumn);

            if (count($postUUIDColumn) < 5) {
                $aPostUUID = [array_rand($postUUIDColumn, 1)]; // 随机给1个动态各发一条回复
            } elseif (count($postUUIDColumn) < 10) {
                $aPostUUID = array_rand($postUUIDColumn, 3); // 随机给N个动态各发一条回复
            } elseif (count($postUUIDColumn) < 20) {
                $aPostUUID = array_rand($postUUIDColumn, 6); // 随机给N个动态各发一条回复
            } else {
                $aPostUUID = array_rand($postUUIDColumn, 9); // 随机给N个动态各发一条回复
            }

            foreach ($aPostUUID as $postUuid) {
                $reviewUUID = $taquConn->table('t_jiaoliuqu_review')
                    ->where('post_uuid', $postUuid)
                    ->where('cj_status', 1)
                    ->order('create_time')
                    ->value('uuid');

                $transReviewRet = CjDataService::translateReviewData($reviewUUID);
                if ($transReviewRet === false) { // 同步失败
                    // 修改源数据状态
                    $taquConn->table('t_jiaoliuqu_review')
                        ->where('uuid', $reviewUUID)
                        ->where('cj_status <> 10')
                        ->update([
                            'cj_status' => 10,
                            'trans_time' => time(),
                            'trans_error' => CjDataService::$errMessage
                        ]);

                    if (CjDataService::$errCode == -101) {
                        $taquConn->table('t_jiaoliuqu_post')->where('uuid', $postUuid)->update([
                            'cj_review_untrans_num' => Db::raw('cj_review_untrans_num-1'),
                        ]);
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            self::exceptionError('发布异常：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 重新同步有变动的用户数据
     * @return bool
     */
    public static function repeatUpdateUserinfo()
    {
        try {
            $taquConn = CjDataService::connectCjtaqu();

            // 取出一条未同步的采用动态
            $accountSelect = $taquConn->table('t_jiaoliuqu_account')
                ->where('cj_status', 5)
                ->where('xchat_user_id > 0')
                ->where('unix_timestamp(cj_update_time) >= trans_time')
                ->order('cj_create_time')
                ->limit(10)
                ->select();
            if (! $accountSelect) { // 未找到需要更新的用户
                return true;
            }

            foreach ($accountSelect as $account) {
                CjDataService::repeatUpdateTranslateAccountData($account['account_uuid']);
            }

            return true;
        } catch (Exception $e) {
            self::exceptionError('发布异常：' . $e->getMessage());
            return false;
        }
    }

}