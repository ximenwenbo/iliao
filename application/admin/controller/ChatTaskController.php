<?php
/**
 * 求撩任务管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use app\admin\service\ChatTaskService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;


class ChatTaskController extends AdminBaseController
{
    /**
     * 求撩列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $type = ChatTaskService::typeList();
        $status = ChatTaskService::statusList();
        $this->assign('type', $type);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * 列表ajax
     * @throws
     */
    public function ListAjax()
    {
        $params = $this->request->param();
        $condition = [
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'type' => empty($params['data']['type']) ? '' : $params['data']['type'],
            'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = ChatTaskService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                //操作按钮
                switch ($val['status']){
                    case 1:
                        $opera = '<button type="button" class="btn social-google-plus btn-sm " id="Del">
                                    <i class="icon wb-warning" aria-hidden="true"></i> 关闭
                                 </button>';
                        break;
                    default:
                        $opera = '<button type="button" class="btn btn-success btn-outline btn-sm" onclick="detailsPopup('.$val['id'].')">
                                    <i class="icon wb-user" aria-hidden="true" ></i> 详情
                                 </button>';
                        break;
                }

                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'type' => ChatTaskService::typeList($val['type']),
                    'status' => ChatTaskService::statusList($val['status']),
                    'user_id' => $val['user_id'],
                    'mobile' => $val['mobile'],
                    'user_nickname' => $val['user_nickname'],
                    'err_subject' => !empty($val['err_subject']) ? $val['err_subject']:'无',
                    'per_cost' => $val['per_cost'],
                    'is_lock' => $val['is_lock'] == 0 ? '未锁' : '已锁',
                    'finish_time' => !empty($val['finish_time']) ? date("Y-m-d H:i",$val['finish_time']) : '无',
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
                    "update_time"=> !empty($val['update_time']) ? date("Y-m-d H:i",$val['update_time']) : '无',
                    'opera' => $opera
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

    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DelInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(['msg'=>'数据不存在','code'=>0]);
        }
        if (Db::name('chat_task')->where('id', $id)->value('status') > 1) {
            return json_encode(['msg'=>'任务已经被接单，不能关闭','code'=>0]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
        ];
        $result = ChatTaskService::UpdateB(['id'=>$id],$condition);
        if($result){
            return json_encode(['msg'=>'删除成功','code'=>200]);
        }else{
            return json_encode(['msg'=>'删除失败','code'=>0]);
        }

    }


    /**
     * 资源列表 - 查看详情
     * @author zjy
     * @throws
     */
    public function Details()
    {
        $id = $this->request->param('id');
        if(empty($id))
        {
            $this->error('参数错误');
        }
        $info = Db::name('chat_order')
                    ->alias('o')
                    ->join('user u','u.id=o.accept_uid')
                    ->field('o.*,u.user_nickname, u.mobile,u.avatar,u.age,u.sex,u.signature,u.be_follow_num,u.be_look_num')
                    ->where(['o.task_id'=>$id])
                    ->find();

        $sex_type = ['保密','男','女'];
        if(!empty($info)){
            $info['sex_type'] = $sex_type[$info['sex']];
            $info['avatar'] = MaterialService::getFullUrl($info['avatar']);
            $info['type'] = ChatTaskService::typeList($info['type']);
            $info['status'] = ChatTaskService::statusList($info['status']);
            $launch_info = UserMemberService::ToInfo(['id'=>$info['launch_uid']],'user_nickname,mobile,avatar,sex,signature,age,be_look_num,be_follow_num');
            if(!empty($launch_info)){
                $launch_info['avatar'] = MaterialService::getFullUrl($launch_info['avatar']);
                $launch_info['sex_type'] = $sex_type[$info['sex']];
                $info['launch_info'] = $launch_info;
            }
        }
        $this->assign('info',$info);
        return $this->fetch();
    }

}
