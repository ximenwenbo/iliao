<?php
/**
 * 商户管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MerchantCustomerService;
use app\admin\service\MerchantManagementService;
use cmf\controller\AdminBaseController;
use think\Request;
use think\Session;


class MerchantManagementController extends AdminBaseController
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
            $result = MerchantManagementService::UList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $admin_id = cmf_get_current_admin_id();
                    $opera = '<button type="button" class="btn btn-warning btn-sm" onclick="editPopup('.$val['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>
                                 <button type="button" class="btn social-google-plus btn-sm " id="DelOne">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                    if($admin_id == 1){
                        $opera .= '&nbsp;<button type="button" class="btn btn-labeled btn-sm" onclick="BecomeMerchant('.$val['id'].')">
                                        <i class="icon fa-instagram" aria-hidden="true"></i> 商户
                                     </button>';
                    }

                    switch ($val['status']){
                        case 0:
                            $status = '<span style="color:red;">暂停中</span>';
                            break;
                        case 1:
                            $status = '<span style="color:green;">合作中</span>';
                            break;
                        default:
                            $status = '未知';
                            break;
                    }
                    //编辑人
                    $filed =  [
                        'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'address' => $val['address'],
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
        $merchant_name = MerchantCustomerService::getMerchantName();
        $this->assign('merchant_name', $merchant_name);
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
            if(!isset($param['name']) || empty($param['name'])){
                return json_encode(['code'=>0, 'msg'=>'商户名称不能为空']);
            }else{
                if(mb_strlen($param['name']) > 50)
                {
                    return json_encode(['code'=>0, 'msg'=>'商户名称不能超过50个字符']);
                }
            }

            if(!isset($param['address']) || empty($param['address'])){
                return json_encode(['code'=>0, 'msg'=>'商户地址不能为空']);
            }

            if(!isset($param['mobile']) || empty($param['mobile'])){
                return json_encode(['code'=>0, 'msg'=>'商户联系方式不能为空']);
            }

            if(mb_strlen($param['address']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'商户地址字符过长']);
            }
            $condition = [
                'name' => $param['name'],
                'address' => $param['address'],
                'mobile' => $param['mobile'],
                'create_time' => time(),
                'update_time' => time(),
                'status' => 1,
            ];
            $result = MerchantManagementService::AddData($condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'添加成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'添加失败','data'=>$result]);
            }
        }

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
            if(!isset($param['name']) || empty($param['name'])){
                return json_encode(['code'=>0, 'msg'=>'商户名称不能为空']);
            }else{
                if(mb_strlen($param['name']) > 50)
                {
                    return json_encode(['code'=>0, 'msg'=>'商户名称不能超过50个字符']);
                }
            }

            if(!isset($param['address']) || empty($param['address'])){
                return json_encode(['code'=>0, 'msg'=>'商户地址不能为空']);
            }

            if(!isset($param['mobile']) || empty($param['mobile'])){
                return json_encode(['code'=>0, 'msg'=>'商户联系方式不能为空']);
            }

            if(mb_strlen($param['address']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'商户地址字符过长']);
            }

            $condition = [
                'name' => $param['name'],
                'address' => $param['address'],
                'mobile' => $param['mobile'],
                'update_time' => time(),
            ];
            $result = MerchantManagementService::UpdateB(['id'=>$param['id']],$condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'修改成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'修改失败','data'=>$result]);
            }
        }
        $id = $this->request->param('id');
        $info = MerchantManagementService::ToInfo($id);
        //var_dump($id);die;
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
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
        $result = MerchantManagementService::UpdateB(['id'=>$id],$condition);
        if(empty($result))
        {
            return json_encode(["status"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["status"=>200, "msg"=>"删除成功!",]);
        }

    }

    /**
     * 成为商户身份/取消
     * @throws
     */
    public function BecomeMerchant(){
        $m_id = $this->request->param('id');
        $admin_id = cmf_get_current_admin_id();
        $custom = MerchantCustomerService::ToInfo(['user_id'=>$admin_id],'id,m_id');
        if($custom){
            if(isset($custom['m_id']) && $custom['m_id'] == $m_id){
                return json_encode(['code'=> 0, 'msg' => '已是当前商户,操作无效']);
            }else{
                $result = MerchantCustomerService::UpdateB(['id'=>$custom['id']],['m_id'=>$m_id]);
                if($result){
                    return json_encode(['code'=> 200, 'msg' => '操作成功']);
                }else{
                    return json_encode(['code'=> 0, 'msg' => '操作失败']);
                }
            }

        }else{
            $result = MerchantCustomerService::AddData(['user_id'=>$admin_id, 'm_id'=>$m_id]);
            if($result){
                return json_encode(['code'=> 200, 'msg' => '操作成功']);
            }else{
                return json_encode(['code'=> 0, 'msg' => '操作失败']);
            }
        }
    }

}
