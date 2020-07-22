<?php
namespace cjdata\cjtaqu;

use think\Db;
use think\Log;
use think\Exception;
/**
 * 采集他趣数据帖子回复类
 */
class Reply extends Base
{
    public function createReplyByPostUuid($postUuid)
    {
        $page = 1;
        $limit = 10;
        do {
            $data = $this->taquapiGetReviewsWithReply($postUuid, $page, $limit);
            if (empty($data['review_list'])) {
                break;
            }

            foreach ($data['review_list'] as $item) {
                 $this->addUpdReplyInfo($item);
            }

            $page++;
//            sleep(1);
        } while(1);

        return 1;
    }

    /**
     *
     * https://gw-cn.jiaoliuqu.com/bbs/v5/Review/getReviewsWithReply?is_poster=2&page=1&sort=asc&limit=10&post_uuid=bdgv6r7m34e49
     */
    public function taquapiGetReviewsWithReply($postUuid, $page = 1, $limit = 10, $sort = 'asc')
    {
        try {
            $this->freshHeaderParams();
            $header = $this->headerParam;
            $id = $this->distinctRequestId;
            $url = "https://ubd-cn.jiaoliuqu.com/bbs/v5/Review/getReviewsWithReply";
            $requestParam = [
                'distinctRequestId' => $id,
                'post_uuid' => $postUuid,
                'is_poster' => 2, // 1:只看楼主 2:所有
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort
            ];
            $url .= '?' . http_build_query($requestParam);
            $result = $this->getdata($url, $header);
            $aResult = json_decode($result, true);
            if (empty($aResult) || empty($aResult['info']['data'])) {
                Log::write(sprintf('%s：采集他趣数据，根据postUuid获取回复列表失败，postUuid：%s，返回错误：%s', __METHOD__, $postUuid, var_export($result, true)), 'error');
                return false;
            }

            return $aResult['info']['data'];
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，根据postUuid获取回复列表系统错误，postUuid：%s，错误：%s', __METHOD__, $postUuid, $e->getMessage()), 'error');
            return false;
        }
    }

    public function addUpdReplyInfo($param)
    {
        try {
            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            // 用户不存在时
            if (! $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_uuid'])->count()) {
                $oUser = new \cjdata\cjtaqu\User;
                if (! $oUser->createAccountInfo($param['account_uuid'])) {
                    // 创建用户数据错误
                    return false;
                }
            }

            // 动态不存在时
            if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $param['post_uuid'])->count()) {
                if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->insert(['uuid' => $param['post_uuid']])) {
                    return false;
                }
            }

            if (! $cjtaquDbConn->table('t_jiaoliuqu_review')->where('uuid', $param['uuid'])->count()) {
                // 不存在，新增
                $addReply = [
                    'uuid' => !empty($param['uuid']) ? $param['uuid'] : '',
                    'post_uuid' => !empty($param['post_uuid']) ? $param['post_uuid'] : '',
                    'account_uuid' => $param['account_uuid'],
                    'content' => !empty($param['content']) ? $param['content'] : '',
                    'wet_count' => !empty($param['wet_count']) ? $param['wet_count'] : '',
                    'cj_status' => 1, // 默认都采用
                    'create_time' => !empty($param['create_time']) ? $param['create_time'] : 0,
                ];

                $cjtaquDbConn->table('t_jiaoliuqu_review')->insert($addReply);

                // 更新动态的未同步回复数量
                $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $param['post_uuid'])->setInc('cj_review_untrans_num', 1);
            }

            return true;

        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，动态回复入库系统错误，param数据：%s，错误：%s', __METHOD__, var_export($param, true), $e->getMessage()), 'error');
            return false;
        }
    }
}