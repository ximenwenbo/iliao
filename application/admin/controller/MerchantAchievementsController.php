<?php
/**
 * 客服绩效
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MerchantAchievementsService;
use app\admin\service\MerchantCustomerService;
use app\admin\service\PayTradeService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;


class MerchantAchievementsController extends AdminBaseController
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
                'class_id' => empty($params['data']['class_id']) ? 1 : $params['data']['class_id'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $user_id = $this->getUserId();
            $result = MerchantAchievementsService::RList($condition,$user_id);
            //var_dump($user_id);die;
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $opera = '<a style="color: #fff;" href="/admin/merchant_achievements/details/keywords/'.$val['beinvite_user_id'].'" target="_blank"><button type="button" class="btn btn-success btn-sm">
                            <i class="icon wb-info-circle" aria-hidden="true"></i> 用户充值详情
                         </button></a>';
                    $total_recharge = Db::name('pay_trade')->where("status = 2 and user_id = {$val['beinvite_user_id']}")->sum('amount');
                    $u1_nickname = empty($val['u1_nickname']) ? '无' : $val['u1_nickname'];
                    $u2_nickname = UserMemberService::ToInfo(['id'=>$val['beinvite_user_id']],'user_nickname',-1);
                    $filed =  [
                        'id' => $val['id'],
                        'invite_user_id_u1_nickname' => $val['invite_user_id'].' / '.$u1_nickname,
                        'beinvite_user_id_u2_nickname' => $val['beinvite_user_id'].' / '. $u2_nickname,
                        'total_recharge' => $total_recharge/100,
                        "create_time"=> date("Y-m-d H:i",$val['create_time']),
                        "opera"=> $opera,
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
        $merchant_name = MerchantCustomerService::getMerchantName();
        $this->assign('merchant_name', $merchant_name);
        return $this->fetch();
    }

    /**
     * 详情
     * @return false|mixed|string
     */
    public function details(){
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = PayTradeService::URList($condition);
            //var_dump($result);die;
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    //操作按钮
                    $opera = '<button type="button" class="btn btn-success btn-sm " onclick="OnDetails(\''.$val['order_no'].'\')">
                            <i class="icon wb-info-circle" aria-hidden="true"></i> 详情
                         </button>';

                    $filed =  [
                        'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                        'id' => $val['id'],
                        'status' => PayTradeService::statusList($val['status']),
                        'user_id' => $val['user_id'],
                        'mobile' => $val['mobile'],
                        'user_nickname' => $val['user_nickname'],
                        'order_no' => !empty($val['order_no']) ? $val['order_no']:'无',
                        'pay_channel' => PayTradeService::channelList($val['trade_channel']),
                        'amount' => $val['amount']/100,
                        'coin' => $val['amount']/10,
                        'subject' => $val['subject'],
                        'extra_id' =>  !empty($val['extra_id']) ? $val['extra_id'] : '无',
                        'error_msg' => !empty($val['error_msg']) ?  $val['error_msg'] : '无',
                        'finish_time' => !empty($val['finish_time']) ? date("Y-m-d H:i",$val['finish_time']) : '无',
                        "create_time"=> date("Y-m-d H:i",$val['create_time']),
                        "update_time"=> !empty($val['update_time']) ? date("Y-m-d H:i",$val['update_time']) : '无',
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
        $param = $this->request->param();
        $this->assign('param',$param);
        return $this->fetch();
    }


    /**
     * 订单详情
     * @throws
     */
    public function orderDetails(){
        $order_no = $this->request->param('order_no');
        $pay_trade = Db::name('pay_trade')
                    ->alias('p')
                    ->field('u.user_nickname,u.mobile,u.avatar,p.*')
                    ->join('user u','u.id=p.user_id')
                    ->where(['p.order_no'=>$order_no])
                    ->find();
        if(!$pay_trade){
            exit('订单号有误');
        }
        $pay_trade['extra'] = json_decode($pay_trade['extra']) ? json_decode($pay_trade['extra'],true) : [];
        if(empty($pay_trade['extra'])){
            exit('订单支付详情出错');
        }
        if($pay_trade['trade_channel'] == 'alipay'){
            $alipay_status = [
                'WAIT_BUYER_PAY'=>'等待买家付款',
                'TRADE_CLOSED'=>'未付款交易超时关闭',
                'TRADE_SUCCESS'=>'支付成功',
                'TRADE_FINISHED'=>'交易结束，不可退款',
            ];
            $pay_trade['extra']['trade_status'] = isset($alipay_status[$pay_trade['extra']['trade_status']]) ? $alipay_status[$pay_trade['extra']['trade_status']] : '';
            $this->assign('alipay_status',$alipay_status);
        }

        //var_dump($pay_trade['extra']);die;
        $this->assign('info',$pay_trade);
        return $this->fetch('/pay_trade/details');
    }

    /**
     * @return bool|string|array
     * @throws
     */
    public function getUserId(){
        $admin_id = cmf_get_current_admin_id();
        $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
        if($m_id === NULL){
            if($admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                exit('您没有权限查看,请联系超级管理员!');
            }
        }else{
            if($m_id == 0 && $admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
                $uid_arr = Db::name('merchant_customer')->where("m_id = {$m_id} and status=1")->field('user_id')->select()->toArray();
            }
        }

        if(!$uid_arr){
            exit("商户下没有客服");
        }
        $uid_in = '';
        foreach ($uid_arr as $k1 => $v1){
            $uid_in .= $v1['user_id'].',';
        }
        $uid_in = substr($uid_in,0,strlen($uid_in)-1);

        return $uid_in;
    }
}
