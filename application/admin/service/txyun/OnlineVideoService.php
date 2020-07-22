<?php
namespace app\admin\service\txyun;

use app\admin\model\OnlineVideoModel;
use app\admin\model\VideoModel;
use think\Db;
use think\Log;
use think\Exception;
use app\admin\service\BaseService;

class OnlineVideoService extends BaseService
{
    /**
     * 获取旁路直播URL
     *
     * @param $streamId
     * @return array
     */
    public static function getOnlineLiveUrl($streamId)
    {
        $trtc = cmf_get_option('trtc');
        $domain = $trtc['b_domain']; // 旁路直播域名

        return [
            "rtmp" => "rtmp://" . $domain . "/live/" . $streamId,
            "http_flv" => "http://" . $domain . "/live/" . $streamId . ".flv",
            "http_m3u8" => "http://" . $domain . "/live/" . $streamId . ".m3u8"
        ];
    }

    /**
     * 直播状态设置
     *
     * @param string $streamId 直播码
     * @param int $status 开关状态 (0 表示禁用，1 表示允许推流，2 表示断流)
     * @return bool
     * @throws Exception
     */
    public static function setLiveStatus($streamId, $status)
    {
        $trtc = cmf_get_option('trtc');
        $appid = $trtc['appid'];
        $apiKey = $trtc['api_key'];
        $time = time();
        $api = 'http://fcgi.video.qcloud.com/common_access';

        $param = [
            'appid' => $appid,
            'interface' => 'Live_Channel_SetStatus',
            't' => $time,
            'sign' => md5($apiKey . $time),
            'Param.s.channel_id' => $streamId, // 直播码
            'Param.n.status' => $status, // 开关状态 (0 表示禁用，1 表示允许推流，2 表示断流)
        ];

        $requestUrl = $api . '?' . http_build_query($param);

        try {
            $result = file_get_contents($requestUrl);
            $aRet = json_decode($result, true);
            if (isset($aRet['ret']) && $aRet['ret'] == 0) {
                Log::write(sprintf('%s：开启&关闭推流失败：%s', __METHOD__, var_export($result, true)),'error');
                self::exceptionError('开启&关闭推流失败', -1011);
            }

            return false;
        } catch (Exception $e) {
            Log::write(sprintf('%s：开启&关闭推流系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('开启&关闭推流系统异常:' . $e->getMessage());
        }
    }

    /**
     * 视频列表方法
     * @param $filter array
     * @author zjy
     * @throws Exception
     * @return array
     */
    public static function currentList($filter)
    {
        //参数处理
        $where['status'] = 1;
        if (isset($filter['keywords']) && !empty($filter['keywords']))
        {
            $where['home_id'] = $filter['keywords'];
        }
        try{
            $field = "GROUP_CONCAT(id) as ids,
                        GROUP_CONCAT(user_id) as uid, 
                        GROUP_CONCAT(stream_id) as streams, 
                        home_id,
                        create_time";
            $order = !empty($filter['sortType']) && !empty($filter['sortField']) ? "{$filter['sortField']} {$filter['sortType']}" : 'id desc';
            $offset = !empty($filter['pageIndex']) ? $filter['pageIndex'] : 0;
            $listRow = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

            $model = new OnlineVideoModel();
            $result = $model->selectAll($where, $field, $order, $offset, $listRow);
            if(!$result){
                throw new Exception('系统异常:' . '查询失败');
            }
            return $result;
        }catch (Exception $e) {
            Log::write(sprintf('%s：系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('系统异常:' . $e->getMessage());
        }

    }

    /**
     * 获取直播用户身份信息
     * @param $user_id int
     * @param $home_id int
     * @return bool|array
     * @throws Exception
     */
    public static function getUserInfo($home_id,$user_id)
    {
        if (!empty($home_id) && $home_id > 0 && !empty($user_id) && $user_id > 0)
        {
            try{
                $launch_uid = Db::name('chat_home')->where(['home_id'=>$home_id,'launch_uid'=>$user_id])->value('id');
                if($launch_uid){
                    return '发起者';
                }
                $accept_uid = Db::name('chat_home')->where(['home_id'=>$home_id,'accept_uid'=>$user_id])->value('id');
                if($accept_uid)
                {
                    return '接收者';
                }
                return '未知';
            }catch (Exception $e){
                Log::write(sprintf('%s：系统异常：%s', __METHOD__, $e->getMessage()),'error');
                throw new Exception('系统异常:' . $e->getMessage());
            }
        }
        return false;
    }
}