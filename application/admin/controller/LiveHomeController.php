<?php
/**
 * 直播房间管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\aliyun\AliyunOssModule;
use app\admin\service\LiveChannelService;
use app\admin\service\LiveHomeService;
use app\admin\service\LiveStreamService;
use app\admin\service\MaterialService;
use app\admin\service\txyun\OnlineVideoService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;


class LiveHomeController extends AdminBaseController
{
    /**
     * 房间列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        //table表单请求数据
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
                'status' => isset($params['data']['status']) ? $params['data']['status'] : 1,
                'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
                'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];

            //调用列表方法
            $result = LiveHomeService::RList($condition);
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-outline btn-info btn-sm" onclick="ManagePopup('.$value['id'].')">
                            <i class="icon wb-library" aria-hidden="true" ></i> 管理
                        </button>
                        <button type="button" class="btn btn-outline btn-info btn-sm" onclick="detailsPopup('.$value['id'].')">
                            <i class="icon wb-library" aria-hidden="true" ></i> 详细
                        </button>
                        <button type="button" class="btn btn-outline btn-info btn-sm" onclick="JumpPopup('.$value['id'].')">
                            <i class="icon wb-eye" aria-hidden="true" ></i> 观众
                        </button>
                        <button type="button" class="btn btn-outline btn-danger btn-sm " id="delete-btn">
                                <i class="icon wb-trash" aria-hidden="true"></i> 删除
                         </button>
                        ';

                    $filed =  [
                        'id' => $value['id'],
                        'user_nickname' => $value['user_nickname'],
                        'user_id' => $value['user_id'],
                        'title' => $value['title'],
                        'channel_id' => empty($value['channel_id']) ? '' : LiveChannelService::ToInfo(['id'=>$value['channel_id']],'name',-1),
                        'cover_img' => empty($value['cover_img']) ? '' : '<img src="'.MaterialService::getFullUrl($value['cover_img']).'" alt="." width="50px" height="50px"/>',
                        'city_name' => $value['city_name'],
                        'live_mode' => $value['live_mode'] == 1 ? '视频直播' : '语音直播',
                        'online_viewer' => $value['online_viewer'],
                        'total_viewer' => $value['total_viewer'],
                        'type' => LiveHomeService::typeList($value['type']),
                        'status' => LiveHomeService::statusList($value['status']),
                        'create_time' => $value['create_time'],
                        'opera' => '<a href="javascript:void(0)" style="text-decoration: none" class="more-operate">更多+</a>'
                    ];
                    array_push($data,$filed);
                }
            }
            return json_encode([
                "pageIndex"=> $params['pageIndex'],//分页索引
                "pageSize"=> $params['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "sortField"=> $condition['sortField'],//排序字段
                "sortType"=> $condition['sortType'],//排序类型
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $params['data']//表单参数
            ]);
        }
        $status_arr = LiveHomeService::statusList();
        $this->assign('status_arr',$status_arr);
        return $this->fetch();
    }


    /**
     * 详细信息
     * @throws
     */
    public function DetailsInfo(){
        $param = $this->request->param();
        if (empty($param['id'])){
            $this->error('参数有误');
        }
        $info = LiveHomeService::ToInfo(['id' => $param['id']]);
        if(empty($info)){
            $this->error('该条数据不存在');
        }
        $info['user_nickname'] = UserMemberService::ToInfo(['id'=>$info['user_id']],'user_nickname',-1);
        $info['abs_cover_img'] = empty($info['cover_img']) ? '' : MaterialService::getFullUrl($info['cover_img']);
        $info['channel_name'] = empty($info['channel_id']) ? '' : LiveChannelService::ToInfo(['id'=>$info['channel_id']],'name',-1);
        $info['type_name'] = LiveHomeService::typeList($info['type']);
        $info['status_str'] = LiveHomeService::statusList($info['status']);
        $this->assign('info',$info);
        return $this->fetch('details');
    }

    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DeleteInfo()
    {
        $param = $this->request->param();
        if(empty($param['id']))
        {
            return json_encode(["status"=>0, "msg"=>"参数错误",]);
        }
        $condition = [
            'status' => -99,
            'update_time' => date("Y-m-d H:i:s",time()),
        ];
        $result = LiveHomeService::UpdateB(['id'=>$param['id']],$condition);
        if(empty($result))
        {
            return json_encode(["code"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["code"=>200, "msg"=>"删除成功!",]);
        }

    }


    /**
     * 管理
     * @throws
     */
    public function LiveManage(){
        $id = $this->request->param('id',0);
        if(empty($id)){
            return $this->error('网络异常');
        }
        $home_info = LiveHomeService::ToInfo(['id' => $id]);
        $stream_info = LiveStreamService::ToInfo(['user_id' => $home_info['user_id'],'status' => 1]);
        $user_info = UserMemberService::ToInfo(['id' =>$home_info['user_id']],'id,user_nickname,avatar,sex,age,mobile');
        if($user_info){
            $user_info['abs_avatar'] = MaterialService::getFullUrl($user_info['avatar']);
            $sex = ['男','女','保密'];
            $user_info['sex_s'] = isset($sex[$user_info['sex']]) ? $sex[$user_info['sex']] : '保密';
            $user_info['url'] = OnlineVideoService::getOnlineLiveUrl($stream_info['stream_id'])['http_m3u8'];
        }
        $this->assign('stream_info',$stream_info);
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }

    /**
     * 直播房间操作
     * @throws
     */
    public function setLiveManage()
    {
        if($this->request->isAjax())
        {
            $param = $this->request->param();
            if(!isset($param['type']) || !isset($param['content']) || !isset($param['stream_id'])){
                return json_encode(['code' => 12, 'msg' => '参数错误']);
            }

            $info = LiveStreamService::ToInfo(['id' => $param['stream_id'],'status' => 1]);
            if(empty($info['status'])){
                return json_encode(['code' => 1012, 'msg' => '直播已关闭']);
            }

            switch ($param['type']){
                case 1:
                    $res = AliyunOssModule::pushMsg4OnLive([(string)$info['user_id']], (string)$info['user_id'],'live_notice', $param['content']);
                    break;
                case 2:
                    $res = AliyunOssModule::pushMsg4OnLive([(string)$info['user_id']], (string)$info['user_id'],'live_close');
                    break;
                default:
                    $res = '';
                    break;
            }
            if($res)
            {
                return json_encode(['code'=>200, 'msg'=>'操作成功', 'data'=>$param]);
            }else{
                return json_encode(['code'=>1013, 'msg'=>'操作失败','data' => $res]);
            }

        }
    }
}
