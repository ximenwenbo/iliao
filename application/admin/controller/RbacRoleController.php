<?php
/**
 * 角色管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\model\AdminMenuModel;
use app\admin\model\RoleModel;
use app\admin\service\RbacRoleService;
use cmf\controller\AdminBaseController;
use think\Db;
use tree\Tree;


class RbacRoleController extends AdminBaseController
{
    /**
     * 列表
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
            $result = RbacRoleService::RList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $item)
                {
                    if($item['id'] == 1){
                        $opera = '超级管理员';
                    }else{
                        $opera =
                                '<button type="button" class="btn btn-info btn-sm btn-outline" onclick="PermissionSettings('.$item['id'].')" >
                                    <i class="icon wb-pencil" aria-hidden="true" ></i> 权限设置
                                </button>&nbsp;&nbsp;&nbsp;'.
                                '<button type="button" class="btn btn-outline btn-primary" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                </button>&nbsp;&nbsp;&nbsp;'.
                                '<button type="button" class="btn btn-outline btn-danger" onclick="Delete('.$item['id'].')">
                                    <i class="icon fa-trash-o" aria-hidden="true" ></i> 删除
                                </button>';
                    }


                    $filed =  [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'uni_code' => $item['uni_code'],
                        'remark' => $item['remark'],
                        'status' => $item['status']==1?'<span style="color: green">正常</span>':'<span style="color: red">禁用</span>',
                        "create_time"=> empty($item['create_time']) ? '' : date("Y-m-d H:i",$item['create_time']),
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
     * 添加角色
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(empty($param['name'])){
                return json_encode(['code'=>0,'msg'=>'角色名称必填']);
            }
            $condition = [
                'name' => $param['name'],
                'status' => $param['status'],
                'create_time' => time(),
            ];

            if(!empty($param['uni_code']) && mb_strlen($param['uni_code']) < 101){
                $condition['uni_code'] = $param['uni_code'];
            }else{
                return json_encode(['code'=>0,'msg'=>'编码不能为空,且不能超过100个字符']);
            }
            if(!empty($param['remark'])){
                if(mb_strlen($param['remark']) < 255){
                    $condition['remark'] = $param['remark'];
                }else{
                    return json_encode(['code'=>0,'msg'=>'角色描述不能超过255个字符']);
                }
            }
            $res = RbacRoleService::AddInfo($condition);
            if($res){
                return json_encode(['code'=>200,'msg'=>'添加成功']);
            }else{
                return json_encode(['code'=>0,'msg'=>'添加失败']);
            }

        }
        return $this->fetch('add');
    }

    /**
     * 编辑角色
     * @throws
     */
    public function edit(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(empty($param['id'])){
                return json_encode(['code'=>0,'msg'=>'角色ID有误']);
            }
            if(empty($param['name'])){
                return json_encode(['code'=>0,'msg'=>'角色名称必填']);
            }
            $condition = [
                'name' => $param['name'],
                'status' => $param['status'],
                'create_time' => time(),
            ];

            if(!empty($param['uni_code']) && mb_strlen($param['uni_code']) <= 100){
                $condition['uni_code'] = $param['uni_code'];
            }else{
                return json_encode(['code'=>0,'msg'=>'编码不能为空,且不能超过100个字符']);
            }
            if(!empty($param['remark'])){
                if(mb_strlen($param['remark']) < 255){
                    $condition['remark'] = $param['remark'];
                }else{
                    return json_encode(['code'=>0,'msg'=>'角色描述不能超过255个字符']);
                }
            }
            $res = RbacRoleService::UpdateInfo(['id'=>$param['id']],$condition);
            if($res){
                return json_encode(['code'=>200,'msg'=>'修改成功']);
            }else{
                return json_encode(['code'=>0,'msg'=>'修改失败']);
            }
        }
        $id = $this->request->param('id');
        $info = RbacRoleService::ToInfo(['id'=>$id]);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 删除
     * @return false|string
     * @throws \think\Exception
     */
    public function Delete(){
        $param = $this->request->param();
        if(empty($param['id']) || !is_numeric($param['id'])){
            return json_encode(['code'=>0,'msg'=>'角色ID有误']);
        }
        $res = RbacRoleService::UpdateInfo(['id'=>$param['id']],['status'=>99]);
        if($res){
            return json_encode(['code'=>200,'msg'=>'删除成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'删除失败']);
        }
    }


    /**
     * 权限设置
     * @throws
     */
    public function PermissionSettings()
    {
        $content = hook_one('admin_rbac_authorize_view');

        if (!empty($content)) {
            return $content;
        }

        $AuthAccess     = Db::name("AuthAccess");
        $adminMenuModel = new AdminMenuModel();
        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $tree       = new Tree();
        $tree->icon = ['│ ', '├─ ', '└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $result = $adminMenuModel->menuCache();

        $newMenus      = [];
        $privilegeData = $AuthAccess->where(["role_id" => $roleId])->column("rule_name");//获取权限表数据

        foreach ($result as $m) {
            $newMenus[$m['id']] = $m;
        }

        foreach ($result as $n => $t) {
            $result[$n]['checked']      = ($this->_isChecked($t, $privilegeData)) ? ' checked' : '';
            $result[$n]['level']        = $this->_getLevel($t['id'], $newMenus);
            $result[$n]['style']        = empty($t['parent_id']) ? '' : 'display:none;';
            $result[$n]['parentIdNode'] = ($t['parent_id']) ? ' class="child-of-node-' . $t['parent_id'] . '"' : '';
        }

        $str = "<tr id='node-\$id'\$parentIdNode  style='\$style'>
                   <td style='padding-left:30px;'><input type='checkbox' name='menuId[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'><span>\$name</span></td>
    			</tr>";
        $tree->init($result);

        $category = $tree->getTree(0, $str);
        $role_name = Db::name('role')->where("id={$roleId}")->value('name');

        $this->assign("category", $category);
        $this->assign("roleId", $roleId);
        $this->assign("role_name", $role_name);
        return $this->fetch('auth');

    }


    /**
     * 角色授权提交
     * @adminMenu(
     *     'name'   => '角色授权提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色授权提交',
     *     'param'  => ''
     * )
     */
    public function authorizePost()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->param("roleId", 0, 'intval');
            if (!$roleId) {
                $this->error("需要授权的角色不存在！");
            }
            if (is_array($this->request->param('menuId/a')) && count($this->request->param('menuId/a')) > 0) {

                Db::name("authAccess")->where(["role_id" => $roleId, 'type' => 'admin_url'])->delete();
                foreach ($_POST['menuId'] as $menuId) {
                    $menu = Db::name("adminMenu")->where(["id" => $menuId])->field("app,controller,action")->find();
                    if ($menu) {
                        $app    = $menu['app'];
                        $model  = $menu['controller'];
                        $action = $menu['action'];
                        $name   = strtolower("$app/$model/$action");
                        Db::name("authAccess")->insert(["role_id" => $roleId, "rule_name" => $name, 'type' => 'admin_url']);
                    }
                }

                cache(null, 'admin_menus');// 删除后台菜单缓存

                $this->success("授权成功！");
            } else {
                //当没有数据时，清除当前角色授权
                Db::name("authAccess")->where(["role_id" => $roleId])->delete();
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        }
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param $privData
     * @return bool
     */
    private function _isChecked($menu, $privData)
    {
        $app    = $menu['app'];
        $model  = $menu['controller'];
        $action = $menu['action'];
        $name   = strtolower("$app/$model/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 获取菜单深度
     * @param $id
     * @param array $array
     * @param int $i
     * @return int
     */
    protected function _getLevel($id, $array = [], $i = 0)
    {
        if ($array[$id]['parent_id'] == 0 || empty($array[$array[$id]['parent_id']]) || $array[$id]['parent_id'] == $id) {
            return $i;
        } else {
            $i++;
            return $this->_getLevel($array[$id]['parent_id'], $array, $i);
        }
    }
}
