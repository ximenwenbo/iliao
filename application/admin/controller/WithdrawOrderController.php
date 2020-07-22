<?php
/**
 * 用户提现管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\UserModel;
use app\admin\model\WithdrawOrderModel;
use app\admin\service\txyun\YuntongxinService;
use app\admin\service\WithdrawOrderService;
use cmf\controller\AdminBaseController;
use think\Request;
use think\Session;


class WithdrawOrderController extends AdminBaseController
{
    /**
     * 礼物列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $typeList = WithdrawOrderService::type();
        $status = WithdrawOrderService::typeList();
        $this->assign('typeList',$typeList);
        $this->assign('status',$status);
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
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'type' => empty($params['data']['type']) ? '' : $params['data']['type'],
            'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = WithdrawOrderService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '';
                //操作按钮
                switch ($val['status'])
                {
                    case 1://审批中
                        $opera = '<button type="button" class="btn btn-success btn-sm btn-outline btn-default" onclick="AuthPopup('.$val['id'].')">
                                    <i class="icon icon wb-user" aria-hidden="true" ></i> 审核
                                 </button>';
                        break;
                    case 2://打款
                        $opera = '<button type="button" class="btn btn-success btn-sm " id="toMoney">
                                    <i class="icon wb-warning" aria-hidden="true"></i> 打款
                                 </button>';
                        break;
                    case 10://审批失败
                        $opera = '<button type="button" class="btn social-google-plus btn-sm " id="authDel">
                                    <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                 </button>';
                        break;
                    default:
                        $opera='';
                        break;
                }

                if(!empty($val['confirm_user'])){
                    $model = new UserModel();
                    $confirm_user = $model->userInfo($val['confirm_user'],'user_login')['user_login'];
                }else{
                    $confirm_user = '无';
                }
                if(!empty($val['auditor'])){
                    $model = new UserModel();
                    $auditor = $model->userInfo($val['auditor'],'user_login')['user_login'];
                }else{
                    $auditor = '无';
                }
                $filed = [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'order_no' => $val['order_no'],
                    'status' => WithdrawOrderService::typeList($val['status']),
                    'user_id' => $val['user_id'],
                    'mobile' => $val['mobile'],
                    'user_nickname' => $val['user_nickname'] . "({$val['user_id']})",
                    'err_msg' => !empty($val['err_msg']) ? $val['err_msg']:'无',
                    'coin' => $val['coin'],
                    'type' => WithdrawOrderService::type($val['type']),
                    'amount' => $val['amount']/100,
                    'payment_amount' => $val['payment_amount']/100,
                    'handing_fee' => $val['handing_fee']/100,
                    'withdraw_account' => $val['withdraw_account'],
                    'withdraw_name' => $val['withdraw_name'],
                    'payment_time' => !empty($val['payment_time']) ? date("Y-m-d H:i",$val['payment_time']) : '无',
                    'auditor' => $auditor,
                    'confirm_user' => $confirm_user,
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
                    "audit_time"=> !empty($val['audit_time']) ? date("Y-m-d H:i",$val['audit_time']) : '无',
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

    /**
     * 用户提现审核
     * @throws
     */
    public function ToExamine()
    {
        //post数据接收
        if(Request::instance()->isPost()){
            $params = Request::instance()->post();
            //数据验证
            if(!isset($params['id']) || empty($params['id']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>1]);
            }
            if(!isset($params['error_msg']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>2]);
            }
            if(!isset($params['status']) || !is_numeric($params['status']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>3]);
            }else{
                if($params['status'] == 10)
                {
                    if(empty($params['error_msg']) || mb_strlen($params['error_msg']) > 255)
                    {
                        return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>4]);
                    }
                }
                else
                {
                    if(strlen($params['error_msg']) > 200)
                    {
                        return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>5]);
                    }
                }
            }
            if (empty(Session::get('ADMIN_ID'))) {
                return json_encode(['code' => 102 ,'msg'=>'请重新登陆']);
            }

            if ($params['status'] == 2) { // 通过
                $result = WithdrawOrderService::approvalSuccess4withdraw($params['id']);
            } elseif ($params['status'] == 10) { // 拒绝
                $result = WithdrawOrderService::approvalFail4withdraw($params['id'], $params['error_msg']);
            }

            if ($result) {
                $uid = WithdrawOrderService::ToInfo(['id' => $params['id']],'user_id',-1);
                if ($params['status'] == 2) { // 通过
                    $res = YuntongxinService::pushSysNotice($uid, 'SYS_WITHDRAW_AUDIT_SUCCESS');
                    if (! $res) {
                        return json_encode(['msg'=>"提现申请审核通过", 'code'=>10]);
                    }
                } elseif ($params['status'] == 10) { // 拒绝
                    $res = YuntongxinService::pushSysNotice($uid, 'SYS_WITHDRAW_AUDIT_FAIL');
                    if (! $res){
                        return json_encode(['msg'=>"提现申请审核失败", 'code'=>20]);
                    }
                }
                return json_encode(['msg'=>"操作成功", 'code'=>200]);
            } else {
                return json_encode(['msg'=>WithdrawOrderService::$errMessage, 'code'=>1]);
            }
        }

        $id = $this->request->param('id');
        $model = new WithdrawOrderModel();
        $info = $model->selectJoinOne(['a.id'=>$id],['a','user u', 'u.id=a.user_id'],'a.*,u.user_nickname,mobile');
        if (empty($info)) {
            return json_encode(['msg'=>"该数据不存在或已过期", 'code'=>1]);
        }
        $this->assign('info',$info);
        return $this->fetch('to_examine');
    }

    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DelInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(['msg'=>'数据不存在','code'=>0]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
            'confirm_user' => Session::get('ADMIN_ID'),
        ];
        $result = WithdrawOrderService::UpdateB(['id'=>$id],$condition);
        if($result){
            return json_encode(['msg'=>'删除成功','code'=>200]);
        }else{
            return json_encode(['msg'=>'删除失败','code'=>0]);
        }
    }

    /**
     * 审批通过 打款
     * @author zjy
     * @throws
     */
    public function toMoney()
    {
        $id = Request::instance()->post('id');
        if (empty($id)) {
            return json_encode(['msg'=>'数据不存在','code'=>0]);
        }

        $result = WithdrawOrderService::paymentWithdraw($id);
        if ($result) {
            return json_encode(['msg'=>'操作成功','code'=>200]);
        } else {
            return json_encode(['msg'=>'操作失败','code'=>0]);
        }
    }

}
