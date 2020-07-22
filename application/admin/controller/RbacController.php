<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use app\admin\model\AdminMenuModel;

class RbacController extends AdminBaseController
{
    /**
     * 角色管理列表
     * @adminMenu(
     *     'name'   => '角色管理',
     *     'parent' => 'admin/User/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '角色管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('admin_rbac_index_view');

        if (!empty($content)) {
            return $content;
        }

        $data = Db::name('role')->order(["list_order" => "ASC", "id" => "DESC"])->select();
        $this->assign("roles", $data);

        return $this->fetch();
    }

    public function ajaxList()
    {
        $data = [];
        $reslut = Db::name('role')->order(["list_order" => "ASC", "id" => "DESC"])->select();

        foreach ($reslut as $v) {
            $data[] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'remark' => $v['remark'],
                'create_time' => $v['create_time'],
                'edit_url' => url('rbac/roleedit', ['id' => $v['id']]),
                'delete_url' => url('rbac/roledelete', ['id' => $v['id']]),
            ];
        }

        echo json_encode(['success'=>true, 'msg'=>'', 'data'=>$data]);die;
    }

    public function ajaxOne()
    {
        $id = $this->request->param('id', 0, 'int');
        $data = Db::name('role')->find($id);

        echo json_encode(['success'=>true, 'msg'=>'', 'data'=>$data]);die;
    }

    /**
     * 添加角色
     * @adminMenu(
     *     'name'   => '添加角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色',
     *     'param'  => ''
     * )
     */
    public function roleAdd()
    {
        $content = hook_one('admin_rbac_role_add_view');

        if (!empty($content)) {
            return $content;
        }

        return $this->fetch();
    }

    /**
     * 添加角色提交
     * @adminMenu(
     *     'name'   => '添加角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加角色提交',
     *     'param'  => ''
     * )
     */
    public function roleAddPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $data['create_time'] = time();
                $data['update_time'] = time();
                $result = Db::name('role')->insert($data);
                if ($result) {
                    $this->success("添加角色成功", url("rbac/index"));
                } else {
                    $this->error("添加角色失败");
                }

            }
        }
    }

    /**
     * 编辑角色
     * @adminMenu(
     *     'name'   => '编辑角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色',
     *     'param'  => ''
     * )
     */
    public function roleEdit()
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        $data = Db::name('role')->where(["id" => $id])->find();
        if (!$data) {
            $this->error("该角色不存在！");
        }
        $this->assign("data", $data);
        return $this->fetch();
    }

    /**
     * 编辑角色提交
     * @adminMenu(
     *     'name'   => '编辑角色提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑角色提交',
     *     'param'  => ''
     * )
     */
    public function roleEditPost()
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'role');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);

            } else {
                if (Db::name('role')->update($data) !== false) {
                    $this->success("保存成功！", url('rbac/index'));
                } else {
                    $this->error("保存失败！");
                }
            }
        }
    }

    /**
     * 删除角色
     * @adminMenu(
     *     'name'   => '删除角色',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除角色',
     *     'param'  => ''
     * )
     */
    public function roleDelete()
    {
        $id = $this->request->param("id", 0, 'intval');
        if ($id == 1) {
            $this->error("超级管理员角色不能被删除！");
        }
        $count = Db::name('RoleUser')->where(['role_id' => $id])->count();
        if ($count > 0) {
            $this->error("该角色已经有用户！");
        } else {
            $status = Db::name('role')->delete($id);
            if (!empty($status)) {
                $this->success("删除成功！", url('rbac/index'));
            } else {
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 设置角色权限
     * @adminMenu(
     *     'name'   => '设置角色权限',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '设置角色权限',
     *     'param'  => ''
     * )
     */
    public function authorize()
    {
        $content = hook_one('admin_rbac_authorize_view');

        if (!empty($content)) {
            return $content;
        }

        //角色ID
        $roleId = $this->request->param("id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $this->assign("roleId", $roleId);
        return $this->fetch();
    }

    public function ajaxAuthorizeList()
    {
        //角色ID
        $roleId = $this->request->param("role_id", 0, 'intval');
        if (empty($roleId)) {
            $this->error("参数错误！");
        }

        $AuthAccess = Db::name("AuthAccess");
        $privilegeData = $AuthAccess->where(["role_id" => $roleId])->column("rule_name");//获取权限表数据

        $adminMenuModel = new AdminMenuModel();
        $result = $adminMenuModel->menuCache();
        $aMenuList = [];
        foreach ($result as $id => $val) {
            $aMenuList[$id] = [
                'id' => $val['id'],
                'parent_id' => $val['parent_id'],
                'text' => $val['name'],
                'icon' => $val['icon'],
                'state' => [
                    'opened' => true,  // is the node open
//                    'disabled' => true,  // is the node disabled
                    'selected' => $this->_isSelected($val, $privilegeData),  // is the node selected
                  ],
            ];
        }

        $retMenus = $this->_generateTree($aMenuList);

        echo json_encode([
            'success'=>true,
            'msg'=>'',
            'data'=>$retMenus
        ]);die;
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
    private function _isSelected($menu, $privData)
    {
        $app    = $menu['app'];
        $model  = $menu['controller'];
        $action = $menu['action'];
        $name   = strtolower("$app/$model/$action");
        if ($privData) {
            if (in_array($name, $privData)) {
                if (Db::name('admin_menu')->where('parent_id', $menu['id'])->count()) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * 生成目录tree结构
     * @param $items
     * @return array
     */
    private function _generateTree($array)
    {
        // 遍历数据 生成树状结构
        $tree = array();
        foreach ($array as $key => $item) {
            if (isset($array[$item['parent_id']])) {
                $array[$item['parent_id']]['children'][] = &$array[$key];
            } else {
                $tree[] = &$array[$key];
            }
        }
        return $tree;
    }

    /**
     * 递归方法，写了4个小时，特地纪念一下--2018-12-20
     * @param $array
     * @return array
     */
    private function _attachSelected($array)
    {
        $ret = [];

        foreach ($array as $k1 => $v1) {

            foreach ($v1 as $k2 => $v2 ) {
                if ($k2 == 'children') {
                    $v2 = $this->_attachSelected($v2);
                    $last_node = 0;
                } else {
                    $last_node = 1;
                }

                $ret[$k1][$k2] = $v2;

                $ret[$k1]['last_node'] = $last_node;
            }
        }


        return $ret;
    }
}

