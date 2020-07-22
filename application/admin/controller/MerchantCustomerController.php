<?php
/**
 * 商户客服管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\model\MerchantManagementModel;
use app\admin\service\MerchantCustomerService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;


class MerchantCustomerController extends AdminBaseController
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
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = MerchantCustomerService::UList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {

                    $opera = '<button type="button" class="btn btn-warning btn-sm" onclick="editPopup('.$val['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>
                                 <button type="button" class="btn social-google-plus btn-sm " id="DelOne">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                    switch ($val['status']){
                        case 0:
                            $status = '<span style="color:red;">未使用</span>';
                            break;
                        case 1:
                            $status = '<span style="color:green;">使用中</span>';
                            break;
                        default:
                            $status = '未知';
                            break;
                    }
                    //返回datatable字段
                    $filed =  [
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'm_id' => $val['m_id'],
                        'user_id' => $val['user_id'],
                        'user_nickname' => $val['user_nickname'],
                        'mobile' => $val['mobile'],
                        'status' => $status,
                        "create_time"=> date("Y-m-d H:i",$val['create_time']),
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
     * 添加
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            //数据验证
            if(!isset($param['user_id']) || empty($param['user_id'])){
                return json_encode(['code'=>0, 'msg'=>'客服UID不能为空']);
            }

            if(!isset($param['m_id']) || empty($param['m_id'])){
                return json_encode(['code'=>0, 'msg'=>'商户不能为空']);
            }

            $condition = [
                'user_id' => $param['user_id'],
                'm_id' => $param['m_id'],
                'create_time' => time(),
                'status' => 1,
            ];
            $result = MerchantCustomerService::AddData($condition);
            if($result){
                return json_encode(['code'=>200,'msg'=>'添加成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'添加失败']);
            }
        }

        $management = Db::name('merchant_management')->where(['status'=>1])->field('name,id')->select()->toArray();
        $this->assign('management',$management);
        return $this->fetch('add');
    }


    /**
     * 编辑
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
            if(!isset($param['user_id']) || empty($param['user_id'])){
                return json_encode(['code'=>0, 'msg'=>'客服UID不能为空']);
            }

            if(!isset($param['m_id']) || empty($param['m_id'])){
                return json_encode(['code'=>0, 'msg'=>'商户不能为空']);
            }

            $condition = [
                'user_id' => $param['user_id'],
                'm_id' => $param['m_id'],
                'update_time' => time(),
                'status' => 1,
            ];
            $result = MerchantCustomerService::UpdateB(['id'=>$param['id']],$condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'修改成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'修改失败','data'=>$result]);
            }
        }
        $id = $this->request->param('id');
        $info = MerchantCustomerService::ToInfo($id);
        //var_dump($id);die;
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
        $management = Db::name('merchant_management')->where(['status'=>1])->field('name,id')->select()->toArray();
        $this->assign('management',$management);
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 删除
     * @author zjy
     * @throws
     */
    public function DelInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在",]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
        ];
        $result = MerchantCustomerService::UpdateB(['id'=>$id],$condition);
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
     * 查询用户是否有效
     * @throws
     */
    public function getUserNickname(){
        $user_id = $this->request->param('user_id');
        $user_info = UserService::ToInfo(['id'=>$user_id],'user_nickname,mobile,id');
        if($user_info){
            $custom_id = MerchantCustomerService::ToInfo(['user_id'=>$user_info['id']],'id',1);
            if($custom_id){
                return json_encode(["code"=>0, "msg"=>"该客服已绑定商户!",]);
            }else{
                return json_encode(["code"=>200, "msg"=>"查询成功!",'data'=>$user_info]);
            }
        }else{
            return json_encode(["code"=>0, "msg"=>"用户信息不存在!",]);
        }
    }
}
