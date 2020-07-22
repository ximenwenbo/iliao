<?php
namespace cjdata\cjtaqu;

use think\Db;
use think\Log;
use think\Exception;
/**
 * 采集他趣数据帖子类
 */
class Post extends Base
{
    public $newNum = 0; //新增的动态数
    public $repeatNum = 0; //重复的动态数

    /**
     * 生成动态列表
     * @param $accountUuid
     * @return int
     */
    public function createPostsByAccountUuid($accountUuid)
    {
        $page = 1;
        $limit = 10;
        $oReply = new \cjdata\cjtaqu\Reply;

        do {
            $data = $this->taquapiGetAccountPostList($accountUuid, $page, $limit);
            if (empty($data['list'])) {
                break;
            }

            foreach ($data['list'] as $item) {
                if (! $this->addUpdPostInfo($accountUuid, $item)) {
                    continue;
                }

                // 生成动态的回复列表
                $oReply->createReplyByPostUuid($item['uuid']);
            }

            $page++;
            sleep(10);
        } while(1);

        return 1;
    }

    protected function taquapiGetAccountPostList($accountUuid, $page = 1, $limit = 10, $sort = 'asc')
    {
        try {
            $this->freshHeaderParams();
            $header = $this->headerParam;
            $distinctRequestId = $this->distinctRequestId;
            $apiurl = "https://ubd-cn.jiaoliuqu.com/bbs/v5/Account/getAccountPostList";
            $requestParam = [
                'distinctRequestId' => $distinctRequestId,
                'account_uuid' => $accountUuid,
                'api_version' => 2,
                'is_video' => 0,
                'page' => $page,
                'limit' => $limit,
                'sort' => $sort
            ];
            $apiurl .= '?' . http_build_query($requestParam);
            $result = $this->getdata($apiurl, $header);
            $aResult = json_decode($result, true);
            if (isset($aResult['response_status']) && $aResult['response_status'] == 'success' && !empty($aResult['info']['data'])) {
                return $aResult['info']['data'];
            }

            return false;
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，根据uuid获取动态列表系统错误，用户uuid：%s，错误：%s', __METHOD__, $accountUuid, $e->getMessage()), 'error');
            return false;
        }
    }

    /**
     * 新增或更新动态内容
     * @param $accountUuid
     * @param $param
     * @return bool|int|string
     */
    public function addUpdPostInfo($accountUuid, $param)
    {
        try {
            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            if (! $cjtaquDbConn->table('t_jiaoliuqu_post')->where('uuid', $param['uuid'])->count()) {
                // 不存在，新增
                $addDynamic = [
                    'uuid' => $param['uuid'],
                    'account_uuid' => $accountUuid,
                    'title' => !empty($param['title']) ? $param['title'] : '',
                    'content' => !empty($param['content']) ? $param['content'] : '',
                    'img_list' => !empty($param['img_list']) ? json_encode($param['img_list'], true) : '',
                    'media_list' => !empty($param['post_media']) ? json_encode($param['post_media'], true) : '',
                    'city' => !empty($param['city']) ? $param['city'] : '',
                    'circle_id' => !empty($param['circle_id']) ? $param['circle_id'] : 0,
                    'wet_count' => !empty($param['wet_count']) ? $param['wet_count'] : 0,
                    'create_time' => !empty($param['create_time']) ? $param['create_time'] : '',
                    'update_time' => !empty($param['update_time']) ? $param['update_time'] : 0,
                ];

                if ($cjtaquDbConn->table('t_jiaoliuqu_post')->insert($addDynamic)) {
                    $this->newNum++;
                    return true;
                } else {
                    return false;
                }
            } else {
                $this->repeatNum++;
            }

            return true;

        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，用户详情入库系统错误，param数据：%s，错误：%s', __METHOD__, var_export($param, true), $e->getMessage()), 'error');
            return false;
        }
    }
}