<?php
/**
 * 充值记录管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\PayTradeService;
use cmf\controller\AdminBaseController;
use think\Db;


class PayTradeController extends AdminBaseController
{

    /**
     * 用户充值记录
     * @throws
     */
    public function index(){
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
                    $opera = '<button type="button" class="btn btn-success btn-outline btn-sm " onclick="OnDetails(\''.$val['order_no'].'\')">
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
        return $this->fetch('details');
    }


}
