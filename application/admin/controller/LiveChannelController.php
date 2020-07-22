<?php
/**
 * 后台管理员
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\LiveChannelService;
use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Session;
use think\Validate;


class LiveChannelController extends AdminBaseController
{
    /**
     * 管理员列表
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
                'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
                'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];
            //var_dump($condition);die;
            $result = LiveChannelService::RList($condition);
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$value['id'].')">
                            <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                        </button>
                        <button type="button" class="btn btn-outline btn-danger btn-sm" onclick="deletePopup('.$value['id'].')">
                                <i class="icon wb-warning" aria-hidden="true"></i> 删除
                         </button>
                        ';

                    //图标
                    $icon_abs = MaterialService::getFullUrl($value['icon']);
                    $icon = '<div onclick="ZoomDisplay('."'头像'".", '{$icon_abs}'".')"><img src="'.$icon_abs.'" alt=""  width="50" height="50"></div>';

                    $filed =  [
                        'id' => $value['id'],
                        'name' => $value['name'],
                        'icon' => $icon,
                        'description' => $value['description'],
                        'status' => LiveChannelService::statusList($value['status']),
                        'create_time' => $value['create_time'],
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
        return $this->fetch();
    }


    /**
     * 添加频道
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            //获取post数据 转化为数组
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            //字段验证
            $validate = new Validate([
                'name' => 'require|max:100',
                'description' => 'require|max:255',
                'status' => 'require|between:0,1',
                'icon' => 'require|max:255',
            ]);
            $validate->message([
                'name.require' => '频道名不能为空',
                'name.max' => '频道名最多100个字符',
                'description.require' => '频道介绍不能为空',
                'description.max' => '介绍最多255个字符',
                'status.between' => '数据状态有误',
                'icon.require' => '频道图标不能为空',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            //入库字段
            $condition = [
                'name' => $params['name'],
                'description' => $params['description'],
                'icon' => $params['icon'],
                'status' => $params['status'],
            ];
            $Nresult = LiveChannelService::AddData($condition);
            if($Nresult){
                return json_encode(['msg' => '添加成功', 'code'=> 200]);
            }else{
                return json_encode(['msg' => '添加失败', 'code'=> 0]);
            }


        }
        $admin_id = Session::get('ADMIN_ID');
        $this->assign('admin_id',$admin_id);
        return $this->fetch('add');
    }

    /**
     * 编辑频道
     * @throws
     */
    public function EditInfo(){
        /*保存编辑请求*/
        if($this->request->isAjax()){
            //获取post数据 转化为数组
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            //字段验证
            $validate = new Validate([
                'id' => 'require',
                'name' => 'require|max:100',
                'description' => 'require|max:255',
                'status' => 'require|between:0,1',
                'icon' => 'require|max:255',
            ]);
            $validate->message([
                'id.require' => 'id is require',
                'name.require' => '频道名不能为空',
                'name.max' => '频道名最多100个字符',
                'description.require' => '频道介绍不能为空',
                'description.max' => '介绍最多255个字符',
                'status.between' => '数据状态有误',
                'icon.require' => '频道图标不能为空',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            //修改字段
            $condition['update_time'] = date("Y-m-d H:i:s",time());
            $info = LiveChannelService::ToInfo(['id'=>$params['id']]);
            if(empty($info)){
                return json_encode(['code'=>0, 'msg'=> '该条数据有变化,请刷新后重试']);
            }else{
                //匹配新数据和原数据 不一致时才修改
                foreach ($params as $k1 => $v1){
                    foreach ($info as $k2 => $v2){
                        if($k1 == $k2){
                            if(gettype($v2) == 'integer'){ //获取查询信息变量的类型 integer需要转化类型入库
                                if((int)$v1 != $v2){
                                    $condition[$k1] = (int)$v1;
                                }
                            }else{
                                if($v1 != $v2){
                                    $condition[$k1] = $v1;
                                }
                            }
                        }
                    }
                }
            }
            //var_dump($info);die;
            $result = LiveChannelService::UpdateB(['id'=>$params['id']],$condition);
            if($result){
                return json_encode(['code' => 200, 'msg'=> '保存成功']);
            }else{
                return json_encode(['code' => 0, 'msg'=> '保存失败']);
            }
        }

        /*编辑页面*/
        $param = $this->request->param();
        if(empty($param['id'])){
            $this->error('参数有误,请联系管理员');
        }else{
            $info = LiveChannelService::ToInfo(['id'=>$param['id']]);
            if(empty($info)){
                $this->error('频道数据不存在,请检查数据');
            }
            $info['abs_icon'] = MaterialService::getFullUrl($info['icon']);
        }
        $admin_id = Session::get('ADMIN_ID');
        $this->assign('admin_id',$admin_id);
        $this->assign('info', $info);
        return $this->fetch('edit');
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
        $result = LiveChannelService::UpdateB(['id'=>$param['id']],$condition);
        if(empty($result))
        {
            return json_encode(["code"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["code"=>200, "msg"=>"删除成功!",]);
        }

    }
}
