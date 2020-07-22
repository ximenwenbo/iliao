<?php
/**
 * 旁路直播
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\UserModel;
use app\admin\service\MaterialService;
use app\admin\service\aliyun\AliyunOssModule;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use app\admin\service\txyun\OnlineVideoService;


class OnlineVideoController extends AdminBaseController
{
    /**
     * 进行中的视频聊天
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax())
        {
            //接收参数
            $param = $this->request->param();
            $condition = [
                'keywords' => isset($param['data']['keywords']) ? $param['data']['keywords'] : '',
                'sortField' => 'id',
                'sortType' => isset($param['sortType']) ?  $param['sortType'] : 'desc',
                'offset' => isset($param['pageIndex']) ?  $param['pageIndex'] : 0,
                'pageSize' => isset($param['pageSize']) ?  $param['pageSize'] : 0,
            ];
            $result = OnlineVideoService::currentList($condition);
            $data = [];

            if(!empty($result['data']))
            {
                foreach ($result['data'] as $item)
                {
                    $user_ids = explode(',', $item['uid']);
                    if(count($user_ids) > 1){
                       $id_1 = UserModel::getUserInfo($user_ids[0],'user_nickname');
                       $id_2 = UserModel::getUserInfo($user_ids[1],'user_nickname');
                    }
                    //按钮
                    $opera = '<button type="button" class="btn btn-success btn-sm btn-outline btn-default" onclick="VideoPopup('."'{$item['home_id']}'".')">
                                  <i class="icon wb-video" aria-hidden="true" ></i> 查看
                              </button>&nbsp;&nbsp;
                             ';
                    $nickname_1 = isset($id_1['user_nickname']) && !empty($id_1['user_nickname']) ? $id_1['user_nickname'] : '无';
                    $nickname_2 = isset($id_2['user_nickname']) && !empty($id_2['user_nickname']) ? $id_2['user_nickname'] : '无';
                    /*if($nickname_1 == '无' || $nickname_2 == '无'){
                        continue;
                    }*/
                    //ajax渲染字段
                    $field = [
                        /*'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',*/
                        'id' => $item['ids'],
                        'home' => $item['home_id'],
                        'users' => $nickname_1 . ' -> ' . $nickname_2 ,
                        'time' => date("Y-m-d H:i", $item['create_time']),
                        'opera' => $opera,
                    ];
                    array_push($data,$field);
                }
                //var_dump($data);die;
            }
            //返回数据
            return json_encode([
                "pageIndex"=> $param['pageIndex'],//分页索引
                "pageSize"=> $param['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "total"=> empty($result['total']) ? 0 : $result['total'],//总记录数
                'pageList'=> $data,//分页数据
                "data"=> $param['data']//表单参数
            ]);
        }
        return $this->fetch();
    }

    /**
     * 查看直播 验证成功后打开直播页面
     */
    public function ViewVideo()
    {
        $home_id = $this->request->post('home_id');
        $is_video = Db::name('chat_trtc')->where(['home_id'=>$home_id,'status'=>1])->value('home_id');
        if(empty($is_video)){
            return json_encode(['code'=>0, 'msg'=>'视频已关闭']);
        }
        return json_encode(['code'=>200, 'msg' => '成功']);
    }

    /**
     * 直播页面
     * @throws
     */
    public function onlineVideo(){
        $home_id = $this->request->get('id');
        $stream = Db::name('chat_trtc')->where(['home_id'=>$home_id,'status'=>1])->select()->toArray();
        if(empty($stream)){
            exit('视频已关闭');
        }
        $data = [];
        foreach ($stream as $key=>$item){
            $user = UserModel::getUserInfo($item['user_id'], 'id, user_nickname, age, sex, mobile, avatar');
            $user['avatar'] = !empty($user['avatar']) ? MaterialService::getFullUrl($user['avatar']) : '';
            $data[] = [
                'user' =>  $user,
                'identity' => OnlineVideoService::getUserInfo($item['home_id'],$item['user_id']),
                'url' => OnlineVideoService::getOnlineLiveUrl($item['stream_id'])['http_m3u8'],
                'stream_id' => $item['stream_id'],
                'home_id' => $item['home_id']
            ];
        }


        $sex = ['保密','男', '女'];
        $this->assign('sex', $sex);
        $this->assign('launch', $data[0]);
        $this->assign('accept', $data[1]);
        return $this->fetch('video');
    }

    /**
     * 直播操作
     * @throws Exception
     */
    public function setVideo()
    {
        if($this->request->isAjax())
        {
            $param = $this->request->param();
            if(!isset($param['type']) || !isset($param['content']) || !isset($param['home_id'])){
                return json_encode(['code'=>12, 'msg'=>'参数错误']);
            }

            $info = Db::name('chat_trtc')->where(['home_id'=>$param['home_id']])->find();
            if($info['status'] == 0){
                return json_encode(['code'=>1012, 'msg'=>'直播已关闭']);
            }
            $users = Db::name('chat_home')->where(['home_id'=>$info['home_id']])->field('launch_uid,accept_uid')->find();
            $user_id = [
                (string)$users['launch_uid'],
                (string)$users['accept_uid'],
            ];
            switch ($param['type']){
                case 1:
                    $res = AliyunOssModule::pushMsg4OnLive($user_id, (string)$info['home_id'],'trtc_notice', $param['content']);
                    break;
                case 2:
                    $res = AliyunOssModule::pushMsg4OnLive($user_id, (string)$info['home_id'],'trtc_close');
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
        $id = $this->request->get('id');
        $this->assign('id', $id);
        return $this->fetch('set_video');
    }
}
