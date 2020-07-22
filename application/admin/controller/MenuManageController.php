<?php
/**
 * 菜单管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\AdminMenuModel;
use app\admin\service\MenuManageService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Validate;
use tree\Tree;

class MenuManageController extends AdminBaseController
{
    /**
     * 系统菜单
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'first_class' => empty($params['data']['first_class']) ? '' : $params['data']['first_class'],
                'status' => !isset($params['data']['status']) ? '' : $params['data']['status'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = MenuManageService::RList($condition);
            //var_dump($params);die;
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $status = MenuManageService::statusList($val['status']);
                    $filed =  [
                        'id' => $val['id'],
                        'parent_id' => $val['parent_id'],
                        'name' => $val['name'],
                        'url' => $val['app'].'/'.$val['controller'].'/'.$val['action'],
                        'type' => MenuManageService::typeList($val['type']),
                        'icon' => '<i class="site-menu-icon '.$val['icon'].'" aria-hidden="true"></i>',
                        'list_order' => $val['list_order'],
                        'status' => $val['status'] == 0 ? '<span style="color: #C9C5C5;">'.$status.'</span>' : $status,
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
        //状态
        $status = MenuManageService::statusList();
        $this->assign('status',$status);
        //菜单级
        $menu = MenuManageService::getMenuTree();
        $this->assign('menu',$menu);
        return $this->fetch();
    }

    /**
     * 添加
     * @throws
     */
    public function addInfo(){
        $tree     = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result   = Db::name('AdminMenu')->order(["list_order" => "ASC"])->select();
        $array    = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign("select_category", $selectCategory);

        //状态
        $status = MenuManageService::statusList();
        $this->assign('status',$status);
        //类型
        $type = MenuManageService::typeList();
        $this->assign('type',$type);
        return $this->fetch('add');
    }

    /**
     * 添加Post
     */
    public function addInfoPost(){
        if ($this->request->isPost()) {
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['parent_id','number|require','上级菜单选择有误|上级菜单选择有误'],
                ['name','require|max:30','名称必填|名称不能超过30个字符'],
                ['app','require|max:40|alphaNum','应用必填|应用不能超过40个字符|应用必须由字母或数字组成'],
                ['controller','require|max:30|alphaNum','控制器必填|控制器不能超过30个字符|控制器必须由字母或数字组成'],
                ['action','require|max:30|alphaNum','方法必填|方法不能超过30个字符|方法必须由字母或数字组成'],
                ['list_order','number','排序必须为数字'],
                ['remark','max:30','备注不能超过255个字符'],
                ['param','max:50','备注不能超过50个字符'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            Db::startTrans();
            try {
                $res = Db::name('AdminMenu')->strict(false)->field(true)->insert($params);
                if(!$res) {
                    $this->error('菜单列表添加失败');
                }
                $findAuthRuleCount = Db::name('auth_rule')->where([
                    'app'  => $params['app'],
                    'name' => "{$params['app']}/{$params['controller']}/{$params['action']}",
                    'type' => 'admin_url'
                ])->count();
                $condition = [
                    "name"  => "{$params['app']}/{$params['controller']}/{$params['action']}",
                    "app"   => $params['app'],
                    "type"  => "admin_url", //type 1-admin rule;2-user rule
                    "title" => $params['name'],
                    'param' => $params['param'],
                ];
                if (empty($findAuthRuleCount)) {
                    $insert = Db::name('AuthRule')->insert($condition);
                    if(!$insert){
                        $this->error('权限列表添加失败');
                    }
                }
                Db::commit();
                $this->success("添加成功！");
            }catch (Exception $exception) {
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
        $tree   = new Tree();
        $id     = $this->request->param("id", 0, 'intval');
        $rs     = Db::name('AdminMenu')->where(["id" => $id])->find();
        $result = Db::name('AdminMenu')->order(["list_order" => "ASC"])->select();
        $array  = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $rs['parent_id'] ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        //状态
        $status = MenuManageService::statusList();
        $this->assign('status',$status);
        //类型
        //var_dump($rs['status']);die;
        $type = MenuManageService::typeList();
        $this->assign('type',$type);
        $this->assign("data", $rs);
        $this->assign("select_category", $selectCategory);
        return $this->fetch();
    }

    /**
     * 编辑post
     */
    public function editPost(){
        if ($this->request->isPost()) {
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['id','require','系统ID错误'],
                ['parent_id','number|require','上级菜单选择有误|上级菜单选择有误'],
                ['name','require|max:30','名称必填|名称不能超过30个字符'],
                ['app','require|max:40|alphaNum','应用必填|应用不能超过40个字符|应用必须由字母或数字组成'],
                ['controller','require|max:30|alphaNum','控制器必填|控制器不能超过30个字符|控制器必须由字母或数字组成'],
                ['action','require|max:30|alphaNum','方法必填|方法不能超过30个字符|方法必须由字母或数字组成'],
                ['list_order','number','排序必须为数字'],
                ['remark','max:30','备注不能超过255个字符'],
                ['param','max:50','备注不能超过50个字符'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            Db::startTrans();
            try {
                $oldMenu = Db::name('AdminMenu')->where(['id' => $params['id']])->find();
                $res = Db::name('AdminMenu')->strict(false)->field(true)->update($params);
                if(!$res) {
                    $this->error('菜单列表修改失败');
                }
                $where = [
                    'app'  => $params['app'],
                    'name' => "{$params['app']}/{$params['controller']}/{$params['action']}",
                    'type' => 'admin_url'
                ];
                $findAuthRuleCount = Db::name('auth_rule')->where($where)->count();
                $condition = [
                    "name"  => "{$params['app']}/{$params['controller']}/{$params['action']}",
                    "app"   => $params['app'],
                    "type"  => "admin_url", //type 1-admin rule;2-user rule
                    "title" => $params['name'],
                    'param' => $params['param'],
                ];
                if (empty($findAuthRuleCount)) {
                    $oldName       = "{$oldMenu['app']}/{$oldMenu['controller']}/{$oldMenu['action']}";
                    $findOldRuleId = Db::name('AuthRule')->where(["name" => $oldName])->value('id');
                    if (empty($findOldRuleId)) {
                        Db::name('AuthRule')->insert($condition);
                    } else {
                        Db::name('AuthRule')->where(['id' => $findOldRuleId])->update($condition);
                    }
                } else {
                    Db::name('AuthRule')->where($where)->update(["title" => $params['name'], 'param' => $params['param']]);
                }
                Db::commit();
                $this->success("修改成功！");
            }catch (Exception $exception) {
                // 回滚事务
                Db::rollback();
                $this->error('操作异常：'.$exception->getMessage());
            }
        }
    }

    /**
     * 删除
     * @throws
     */
    public function delete(){
        $id    = $this->request->param("id", 0, 'intval');
        $count = Db::name('AdminMenu')->where(["parent_id" => $id])->count();
        if ($count > 0) {
            $this->error("该菜单下还有子菜单，无法删除！");
        }
        if (Db::name('AdminMenu')->delete($id) !== false) {
            $this->success("删除菜单成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}
