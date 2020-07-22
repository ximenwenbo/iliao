<?php
/**
 * 代理商管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\AgentUserModel;
use app\admin\service\AgentPromotersService;
use app\admin\service\AgentUserService;
use app\admin\service\MaterialService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\Validate;

class AgentPromotersController extends AdminBaseController
{
    /**
     * 代理商
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'status' => !isset($params['data']['status']) ? '' : $params['data']['status'],
                'start_time' => empty($params['data']['startDate']) ? '' : $params['data']['startDate'],
                'end_time' => empty($params['data']['endDate']) ? '' : $params['data']['endDate'],
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $admin_id = cmf_get_current_admin_id();
            if($admin_id != 1){
                $condition['agent_id'] = AgentUserService::ToInfo(['id'=>$admin_id],'admin_uid',-1);
            }
            $result = AgentPromotersService::RList($condition);
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $status = AgentUserService::statusList($val['status']);
                    $filed =  [
                        'id' => $val['id'],
                        'agent_id' => AgentUserService::ToInfo(['id'=>$val['agent_id']],'name',-1),
                        'user_id' => $val['user_id'],
                        'invite_divide_into' => $val['invite_divide_into'],
                        'recharge_divide_into' => $val['recharge_divide_into'],
                        'anchor_divide_into' => $val['anchor_divide_into'],
                        'create_time' => $val['create_time'],
                        'status' => $val['status'] == 0 ? '<span style="color: red;">'.$status.'</span>' : $status,
                        'opera' => '<a href="javascript:void(0)" style="text-decoration: none" class="more-operate">更多+</a>'
                    ];
                    array_push($data,$filed);
                }
            }

            return json_encode([
                "pageIndex"=> $params['pageIndex'],//分页索引
                "pageSize"=> $params['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "sortField"=> 'id',//排序字段
                "sortType"=> 'desc',//排序类型
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $params['data']//表单参数
            ]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     * @throws
     */
    public function addInfo(){
        //获取代理商名称
        $admin_id = cmf_get_current_admin_id();
        $model = new AgentUserModel();
        if($admin_id == 1){
            $where = ['status'=>1];
        }else{
            $where = [
                'status' => 1,
                'admin_uid' =>$admin_id
            ];
        }
        $agent = $model->selectData($where,'name,id');
        $this->assign('agent',$agent);
        return $this->fetch('add');
    }

    /**
     * 添加post
     * @throws Exception
     */
    public function addInfoPost(){
        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['agent_id','require','请选择代理商'],
                ['user_id','require','请填写推广员id'],
                ['invite_divide_into','require|integer','分成必填|分成必须为整数'],
                ['recharge_divide_into','require|integer','分成必填|分成必须为整数'],
                ['anchor_divide_into','require|integer','分成必填|分成必须为整数'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }

            Db::startTrans();
            try{
                //推广员id是否已存在
                if(AgentPromotersService::ToInfo(['user_id' => $params['user_id']])){
                    $this->error('推广员已存在代理商');
                }

                $condition = [
                    'user_id' => $params['user_id'],
                    'agent_id' => $params['agent_id'],
                    'invite_divide_into' => $params['invite_divide_into'],
                    'recharge_divide_into' => $params['recharge_divide_into'],
                    'anchor_divide_into' => $params['anchor_divide_into'],
                ];
                $result = AgentPromotersService::AddData($condition);
                if($result){
                    Db::commit();
                    $this->success('添加成功');
                }else{
                    $this->error('添加失败');
                }
            }catch (ErrorException $exception){
                // 回滚事务
                Db::rollback();
                $this->error('添加异常：'.$exception->getMessage());
            }

        }
    }

    /**
     * 编辑
     * @throws
     */
    public function edit(){
        $id = $this->request->param('id',0,'intval');
        if(empty($id)){
            $this->error('系统错误');
        }
        $info = AgentUserService::ToInfo(['id' => $id]);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 编辑post
     * @throws Exception
     */
    public function editPost(){
        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['name','require|max:50','名称必填|名称不能超过50个字符'],
                ['contact','max:20','联系方式不能超过20个字符'],
                ['address','max:255','地址不能超过255个字符'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $result = AgentUserService::UpdateB(['id'=>$params['id']],$params);
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * 分成设置
     * @throws
     */
    public function DividedSetting(){
        if($this->request->isAjax()){
            $param = $this->request->param('content');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['invite_divide_into','require|integer','分成必填|分成必须为整数'],
                ['recharge_divide_into','require|integer','分成必填|分成必须为整数'],
                ['anchor_divide_into','require|integer','分成必填|分成必须为整数'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $result = AgentPromotersService::UpdateB(['id'=>$params['id']],$params);
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $id = $this->request->param('id');
        if(empty($id)){
            $this->error('系统参数错误');
        }
        $info = AgentPromotersService::ToInfo((['id' => $id]),'invite_divide_into,recharge_divide_into,anchor_divide_into,id');
        if(empty($info)){
            $this->error('系统参数错误');
        }
        $this->assign('info',$info);
        return $this->fetch('divided');
    }

    /**
     * 删除
     * @throws
     */
    public function delete(){
        $id = $this->request->param('id');
        if(empty($id)){
            $this->error('系统参数错误');
        }
        $result = AgentPromotersService::DeleteOne((['id' => $id]));
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 获取用户信息
     * @throws
     */
    public function getUserInfo(){
        $id = $this->request->param('id');
        $user = UserMemberService::ToInfo(['id'=>$id],'avatar,user_nickname');
        if($user){
            $user['avatar_abs'] = MaterialService::getFullUrl($user['avatar']);
            return json_encode(['msg'=>'','data'=>$user,'code'=>1]);
        }else{
            $this->error('用户不存在');
        }
    }
}
