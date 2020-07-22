<?php
/**
 * 充值管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MerchantCustomerService;
use app\admin\service\MerchantRechargeService;
use app\admin\service\PayTradeService;
use cmf\controller\AdminBaseController;
use think\Db;


class MerchantRechargeController extends AdminBaseController
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
                'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $user_id = $this->getUserId();
            $result = MerchantRechargeService::UList($condition,$user_id);
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
        $merchant_name = MerchantCustomerService::getMerchantName();
        $this->assign('merchant_name', $merchant_name);
        return $this->fetch();
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

        $res = Db::name('merchant_customer')
            ->alias('a')
            ->join('user_invite_relation r','r.invite_user_id = a.user_id')
            ->where("a.status=1 and a.user_id in($uid_in)")
            ->field('r.beinvite_user_id')
            ->select()->toArray();

        if(!$res){
            exit('商户没有邀请会员');
        }
        $be_user_id = '';
        foreach ($res as $k1 => $v1){
            $be_user_id .= $v1['beinvite_user_id'].',';
        }
        $be_user_id = substr($be_user_id,0,strlen($be_user_id)-1);
        return $be_user_id;
    }

}
