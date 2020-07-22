<?php
/**
 * 举报管理
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;


class InformController extends AdminBaseController
{
    /**
     * 列表
     * @author zjy
     * @throws
     */
    public function index()
    {
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
            'pageSize'  => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType'  => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset'    => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result['total'] = Db::name('inform_reason')->count();
        $where = '';
        if($condition['sortField'] == 0){
            $where = $where.'id ';
        }else{
            $where = $where.'sort ';
        }
        if($condition['sortType'] == 'desc'){
            $where = $where.'desc';
        }else{
            $where = $where.'asc ';
        }
        $result['data'] = Db::name('inform_reason')->order($where)->limit($condition['offset'],$condition['pageSize'])->select();
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $domain=$this->request->domain();
                $opera = '<button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$val['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>
                                 <button type="button" class="btn btn-outline btn-danger btn-sm " id="authDel">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                $filed =  [
                    'id'            => $val['id'],
                    'reason'        => $val['reason'],
                    'sort'          => $val['sort'],
                    "create_time"   => $val['create_time'],
                    'opera'         => $opera
                ];
                array_push($data,$filed);
            }
        }
        return json_encode([
            "pageIndex" => $params['pageIndex'],//分页索引
            "pageSize"  => $params['pageSize'],//每页显示数量
            "totalPage" => count($data),//分页记录
            "sortField" => $condition['sortField'],//排序字段
            "sortType"  => $condition['sortType'],//排序类型
            "total"     => $result['total'],//总记录数
            'pageList'  => $data,//分页数据
            "data"      => $params['data']//表单参数
        ]);
    }

    /**
     * 添加举报类型
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            //数据验证
            if(!isset($param['reason']) || empty($param['reason'])){
                return json_encode(['code'=>0, 'msg'=>'名称不能为空']);
            }else{
                if(mb_strlen($param['reason']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'名称不能超过100个字符']);
                }
            }

            if(!isset($param['sort']) || !is_numeric($param['sort'])){
                return json_encode(['code'=>0, 'msg'=>'排序必须为数字']);
            }else{
                if(!is_numeric($param['sort']))
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }
            $condition = [
                'reason'    => $param['reason'],
                'sort'      => intval($param['sort']),
            ];
            $res = Db::name('inform_reason')->where(['reason'=>$param['reason']])->count();
            if($res > 0){
                return json_encode(['code'=>0, 'msg'=>'该类型名称已存在','data'=>$res]);
            }
            $result = Db::name('inform_reason')->insert($condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'添加数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'添加数据失败','data'=>$result]);
            }
        }
        return $this->fetch('add');
    }

    /**
     * 举报类型编辑
     * @throws
     */
    public function edit()
    {
        //post数据接收
        if($this->request->isAjax()){
            //数据验证
            $param = $this->request->param();
            if(!isset($param['id']) || empty($param['id'])){
                return json_encode(['code'=>0, 'msg'=>'数据已过期']);
            }
            if(!isset($param['reason']) || empty($param['reason'])){
                return json_encode(['code'=>0, 'msg'=>'类型名称不能为空']);
            }else{
                if(mb_strlen($param['reason']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'类型名称不能超过100个字符']);
                }
            }

            if(!isset($param['sort']) || empty($param['sort'])){
                return json_encode(['code'=>0, 'msg'=>'排序不能为空']);
            }else{
                if(!is_numeric($param['sort']))
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }

            $condition = [
                'reason'    => $param['reason'],
                'sort'      => intval($param['sort']),
            ];
            $result = Db::name('inform_reason')->where(['id'=>$param['id']])->update($condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'修改数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'修改数据失败','data'=>$result]);
            }
        }
        $id = $this->request->param('id');
        $info = Db::name('inform_reason')->find($id);
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 删除举报类型
     * @author zjy
     * @throws
     */
    public function DeleteInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在"]);
        }
        $result = Db::name('inform_reason')->delete($id);
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
     * 举报内容列表
     */
    public function indexcontent()
    {
        return $this->fetch();
    }

    /**
     * 举报内容列表ajax
     * @throws
     */
    public function ContentListAjax()
    {
        $params = $this->request->param();
//        dump($params);die;
        $condition = [
            'pageSize'  => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType'  => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset'    => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $where1 = '';$where2 = '';
        if($params['data']['startDate'] != '' && $params['data']['endDate'] != ''){
            $where1 = "create_time between '".$params['data']['startDate']."' and '".$params['data']['endDate']."'";
        }
        if($params['data']['status'] != '-1'){
            $where2 = "status = ".$params['data']['status'];
        }
        $result['total'] = Db::name('inform_content')->where($where1)->where($where2)->count();
        $result['data']  = Db::name('inform_content')->where($where1)->where($where2)->order('id '.$condition['sortType'])->limit($condition['offset'],$condition['pageSize'])->select();
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $domain = $this->request->domain();
                $opera  = '<button type="button" class="btn btn-info btn-outline btn-sm" id="authEdit">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 已处理
                                 </button>
                                 <button type="button" class="btn btn-outline btn-danger btn-sm " id="authDel">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                switch ($val['status']){
                    case 0:
                        $status = '<span style="color:red;">未处理</span>';
                        break;
                    case 1:
                        $status = '<span style="color:green;">已处理</span>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }
                $user_id    = Db::name('user')->where(['id'=>$val['user_id']])->value('user_nickname');
                $be_user_id = Db::name('user')->where(['id'=>$val['be_user_id']])->value('user_nickname');
                $reason_id  = Db::name('inform_reason')->where(['id'=>$val['reason_id']])->value('reason');
                $filed =  [
                    'id'            => $val['id'],
                    'user_id'       => $user_id,
                    'be_user_id'    => $be_user_id,
                    'reason_id'     => $reason_id,
                    'note'          => $val['note'],
                    'update_time'   => $val['update_time'],
                    'status'        => $status,
                    "create_time"   => $val['create_time'],
                    'opera'         => $opera
                ];
                array_push($data,$filed);
            }
        }
        return json_encode([
            "pageIndex" => $params['pageIndex'],//分页索引
            "pageSize"  => $params['pageSize'],//每页显示数量
            "totalPage" => count($data),//分页记录
            "sortField" => $condition['sortField'],//排序字段
            "sortType"  => $condition['sortType'],//排序类型
            "total"     => $result['total'],//总记录数
            'pageList'  => $data,//分页数据
            "data"      => $params['data']//表单参数
        ]);
    }

    /**
     * 删除举报内容
     * @author zjy
     * @throws
     */
    public function DeleteContent()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在"]);
        }
        $result = Db::name('inform_content')->delete($id);
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
     * 处理举报内容
     * @author zjy
     * @throws
     */
    public function editContent()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在"]);
        }
        $result = Db::name('inform_content')->where(['id'=>$id])->update(['status'=>'1']);
        if(empty($result))
        {
            return json_encode(["code"=>0, "msg"=>"改举报已处理过",]);
        }
        else
        {
            return json_encode(["code"=>200, "msg"=>"处理成功!",]);
        }

    }
}
