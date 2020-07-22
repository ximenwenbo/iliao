<?php
/**
 * 代理商管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\AgentUserService;
use app\admin\service\RoleUserService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\exception\ErrorException;
use think\Validate;

class AgentUserController extends AdminBaseController
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
                'start_time' => empty($params['data']['startDate']) ? '' : $params['data']['startDate'],
                'end_time' => empty($params['data']['endDate']) ? '' : $params['data']['endDate'],
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = AgentUserService::RList($condition);
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $status = AgentUserService::statusList($val['status']);
                    $filed =  [
                        'id' => $val['id'],
                        'admin_uid' => $val['admin_uid'],
                        'name' => $val['name'],
                        'contact' => $val['contact'],
                        'address' => $val['address'],
                        'balance' => $val['balance'],
                        'frozen_balance' => $val['frozen_balance'],
                        'used_balance' => $val['used_balance'],
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
                ['name','require|max:50','名称必填|名称不能超过50个字符'],
                ['contact','max:20','联系方式不能超过20个字符'],
                ['address','max:255','地址不能超过255个字符'],
                ['invite_divide_into','require|integer','分成必填|分成必须为整数'],
                ['recharge_divide_into','require|integer','分成必填|分成必须为整数'],
                ['anchor_divide_into','require|integer','分成必填|分成必须为整数'],
                ['remark','max:255','备注不能超过255个字符'],
                ['user_login','require|alphaDash|length:6,50','账号不能为空|账号必须由字母或数字组成|账号长度必须在6-50个字符之间|账号已存在'],
                ['user_login','require|min:6','密码不能为空|密码不能少于6位'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }

            Db::startTrans();
            try{
                //admin_uid
                $admin_condition = [
                    'user_nickname' => $params['name'],
                    'user_login' => $params['user_login'],
                    'user_type' => 1,
                    'create_time' => time(),
                    'user_pass' => cmf_password($params['user_pass']),
                ];
                if(UserMemberService::ToInfo(['user_nickname'=>$params['user_login'],'user_type'=>1])){
                    $this->error('用户名已存在');
                }
                $id = UserMemberService::AddInfo($admin_condition);
                if(!$id){
                    $this->error('账号添加失败');
                }
                //添加用户对应角色
                $user_role = RoleUserService::AddInfo(['user_id' => $id, 'role_id' => 2]);      // todo role_id = 2 为临时 线上需修改
                if(! $user_role){
                    $this->error('用户角色错误');
                }
                $condition = [
                    'admin_uid' => $id,
                    'name' => $params['name'],
                    'contact' => $params['contact'],
                    'address' => $params['address'],
                    'invite_divide_into' => $params['invite_divide_into'],
                    'recharge_divide_into' => $params['recharge_divide_into'],
                    'anchor_divide_into' => $params['anchor_divide_into'],
                    'remark' => $params['remark'],
                ];
                $result = AgentUserService::AddData($condition);
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
     * 后台账户
     * @throws
     */
    public function adminAccount(){
        $id = $this->request->param('id',0);
        if(empty($id)){
            $this->error('系统参数错误');
        }
        $admin_id = AgentUserService::ToInfo(['id' => $id],'admin_uid',-1);
        if(empty($admin_id)){
            $this->error('系统参数错误');
        }
        $info = UserMemberService::ToInfo((['id' => $admin_id]),'user_nickname,id');
        if(empty($info)){
            $this->error('系统参数错误');
        }
        $this->assign('info',$info);
        return $this->fetch('account');
    }

    /**
     * 修改后台登陆密码
     * @throws
     */
    public function PasswordPost(){
        if($this->request->isAjax()){
            $param = $this->request->param('content');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['id','require','系统参数错误'],
                ['user_pass','require|min:6','密码不能为空|密码不能少于6位'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $res = UserMemberService::UpdateInfo(['id'=>$params['id']],['user_pass' => cmf_password($params['user_pass'])]);
            if($res){
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
            $result = AgentUserService::UpdateB(['id'=>$params['id']],$params);
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
        $info = AgentUserService::ToInfo((['id' => $id]),'invite_divide_into,recharge_divide_into,anchor_divide_into,id');
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
        $result = AgentUserService::DeleteOne((['id' => $id]));
        if($result){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}
