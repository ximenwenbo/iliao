<?php
/**
 * 后台管理员
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\AdministratorsService;
use app\admin\service\RoleUserService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;

class AdministratorsController extends AdminBaseController
{
    /**
     * 管理员列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => !isset($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = AdministratorsService::RList($condition);
            //var_dump($res);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $item)
                {
                    if($item['id'] == 1){
                        $opera = '超级管理员';
                    }else{
                        if($item['user_status'] == 1){
                            $opera = '<button type="button" class="btn btn-outline btn-primary" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-info btn-outline" onclick="blockUp('.$item['id'].')">
                                    <i class="icon wb-pencil" aria-hidden="true" ></i> 禁用
                                 </button>';
                        }else{
                            $opera = '<button type="button" class="btn btn-outline btn-primary" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-success btn-outline" onclick="blockUp('.$item['id'].')">
                                    <i class="icon wb-pencil" aria-hidden="true" ></i> 启用
                                 </button>';
                        }
                    }

                    switch ($item['user_status']){
                        case 0:
                            $status = '<span style="color:red;">禁用</span>';
                            break;
                        case 1:
                            $status = '<span style="color:green;">正常</span>';
                            break;
                        default:
                            $status = '未知';
                            break;
                    }

                    $filed =  [
                        'id' => $item['id'],
                        'user_login' => $item['user_login'],
                        'user_nickname' => $item['user_nickname'],
                        'mobile' => $item['mobile'],
                        'last_login_ip' => $item['last_login_ip'],
                        'status' => $status,
                        "create_time"=> empty($item['create_time']) ? '' : date("Y-m-d H:i",$item['create_time']),
                        "last_login_time"=> empty($item['last_login_time']) ? '' : date("Y-m-d H:i",$item['last_login_time']),
                        'opera' => $opera
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
     * 添加管理员
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(empty($param['user_login']) || empty($param['user_pass']) || empty($param['role'])){
                return json_encode(['code'=>0,'msg'=>'*号项必填']);
            }

            if(mb_strlen($param['user_login']) < 5 || mb_strlen($param['user_login']) > 20 || !preg_match("/^[a-z0-9\#]*$/", $param['user_login'])){
                return json_encode(['code'=>0,'msg'=>'账号格式必须为5-20个字母或数字']);
            }

            $condition_user = [
                'user_type' => 1,
                'user_login' => $param['user_login'],
                'user_pass' => cmf_password($param['user_pass']),
                'create_time' => time(),
            ];

            if(!empty($param['user_nickname']) && mb_strlen($param['user_nickname']) <= 20 && preg_match("/^[a-z0-9\#]*$/", $param['user_nickname'])){
                $condition_user['user_nickname'] = trim($param['user_nickname']);
            }else{
                return json_encode(['code'=>0,'msg'=>'昵称格式必须为1-20个字符']);
            }
            if (mb_strlen(trim($param['user_pass'])) < 6) {
                return json_encode(['code'=>0,'msg'=>'请填写至少6位以上的密码,不能使用特殊符号']);
            }
            if(!preg_match("/^[a-z0-9\#]*$/", $param['user_pass'])){
                return json_encode(['code'=>0,'msg'=>'密码必须为数字或者字母,不能使用特殊符号']);
            }
            if(!empty($param['mobile'])){
                $pattern = "/^1[34578]\d{9}$/";
                if (!preg_match($pattern,$param['mobile']))
                {
                    return json_encode(['code'=>0,'msg'=>'请填写正确的手机号']);
                }
                $condition_user['mobile'] = $param['mobile'];
            }
            if(!empty($param['user_email'])){
                $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
                if (!preg_match($pattern,$param['user_email']))
                {
                    return json_encode(['code'=>0,'msg'=>'请填写正确的邮箱']);
                }
                $condition_user['user_email'] = $param['user_email'];
            }
            $role_id = $param['role'];
            /*事务添加2条数据*/
            Db::startTrans();
            try{
                $user_id = AdministratorsService::AddInfo($condition_user);
                if(!$user_id){
                    throw new \Exception('用户添加失败');
                }
                $role_user = RoleUserService::AddInfo(['role_id'=>$role_id,'user_id'=>$user_id]);
                if(!$role_user){
                    throw new \Exception('角色添加失败');
                }
                // 提交事务
                Db::commit();
                return json_encode(['code'=>200,'msg'=>'添加成功']);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json_encode(['code'=>200,'msg'=>'添加失败:']);
            }
        }
        //获取角色id和name
        $role = AdministratorsService::getRole("uni_code LIKE '%_admin' AND status = 1");
        $this->assign('role',$role['data']);
        return $this->fetch('add');
    }


    /**
     * 修改管理员
     * @throws
     */
    public function edit(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(empty($param['id']) || empty($param['user_pass']) || empty($param['role'])){
                return json_encode(['code'=>0,'msg'=>'*号项必填']);
            }

            $condition_user = [
                'user_pass' => cmf_password($param['user_pass']),
            ];
            if (mb_strlen(trim($param['user_pass'])) < 6) {
                return json_encode(['code'=>0,'msg'=>'请填写至少6位以上的密码']);
            }
            if(!empty($param['user_nickname']) && mb_strlen($param['user_nickname']) <= 20 && preg_match("/^[a-z0-9\#]*$/", $param['user_nickname'])){
                $condition_user['user_nickname'] = trim($param['user_nickname']);
            }else{
                return json_encode(['code'=>0,'msg'=>'昵称格式必须为1-20个字符']);
            }
            if(!empty($param['mobile'])){
                $pattern = "/^1[34578]\d{9}$/";
                if (!preg_match($pattern,$param['mobile']))
                {
                    return json_encode(['code'=>0,'msg'=>'请填写正确的手机号']);
                }
                $condition_user['mobile'] = $param['mobile'];
            }
            if(!empty($param['user_email'])){
                $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
                if (!preg_match($pattern,$param['user_email']))
                {
                    return json_encode(['code'=>0,'msg'=>'请填写正确的邮箱']);
                }
                $condition_user['user_email'] = $param['user_email'];
            }

            /*事务修改2条数据*/
            Db::startTrans();
            try{
                $res = AdministratorsService::UpdateInfo(['id'=>$param['id']],$condition_user);
                if(!$res){
                    throw new \Exception('修改用户信息失败');
                }

                $role_id = RoleUserService::ToInfo(['user_id'=>$param['id']],'role_id',1);
                if($role_id != $param['role']){
                    $role_user = RoleUserService::UpdateInfo(['user_id'=>$param['id']],['role_id'=>$param['role']]);
                    if(!$role_user){
                        throw new \Exception('修改角色信息失败');
                    }
                }
                // 提交事务
                Db::commit();
                return json_encode(['code'=>200,'msg'=>'修改成功']);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json_encode(['code'=>0,'msg'=>$e->getMessage()]);
            }
        }
        //获取角色id和name
        $role = AdministratorsService::getRole("uni_code LIKE '%_admin' AND status = 1");
        //管理员信息
        $id = $this->request->param('id');
        $admin_info = AdministratorsService::ToInfo(['id'=>$id]);
        //管理员对应的角色
        $role_id = RoleUserService::ToInfo(['user_id'=>$id],'role_id',1);
        //var_dump($role);die;
        $this->assign('role',$role['data']);
        $this->assign('info',$admin_info);
        $this->assign('role_id',$role_id);
        return $this->fetch();
    }

    /**
     * 账号是否可以使用
     * @throws
     */
    public function getUserLogin(){
        $user_login = $this->request->param('user_login');
        $res = UserService::ToInfo(['user_login'=>$user_login,'user_type'=>1],'id',1);
        if($res){
            return json_encode(['code'=>0,'msg'=>'账号已存在']);
        }else{
            return json_encode(['code'=>200,'msg'=>'可以使用']);
        }
    }

    /**
     * 启用/停用账号
     * @throws
     */
    public function BlockUp(){
        $id = $this->request->param('id');
        if(empty($id)){
            return json_encode(['code'=>0,'msg'=>'参数错误']);
        }
        $status = AdministratorsService::ToInfo(['id'=>$id],'user_status',1);
        if($status == 1){
            $condition = [
              'user_status' => 0,
            ];
        }else{
            $condition = [
                'user_status' => 1,
            ];
        }
        $res = AdministratorsService::UpdateInfo(['id'=>$id],$condition);
        if($res){
            return json_encode(['code'=>200,'msg'=>'操作成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'操作失败']);
        }
    }
}
