<?php
/**
 * 统计总览
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MerchantCustomerService;
use app\admin\service\MerchantStatisticsOverviewService;
use cmf\controller\AdminBaseController;
use think\Db;



class MerchantStatisticsOverviewController extends AdminBaseController
{
    /**
     * 列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $uid_in = $this->getUserId();
        //var_dump($uid_in);die;
        $data = [];
        $today = ['昨天','今天','本周','本月'];
        foreach ($today as $key=>$item){
            $filed =  [
                'day' => $item,
                'member' => MerchantStatisticsOverviewService::memberTotal($key,$uid_in),
                'money' => MerchantStatisticsOverviewService::moneyTotal($key,$uid_in)/100,
                'vip' => MerchantStatisticsOverviewService::VipTotal($key,$uid_in)/100,
                'monetary' => MerchantStatisticsOverviewService::monetaryTotal($key,$uid_in)
            ];
            array_push($data,$filed);
        }
        $user = MerchantStatisticsOverviewService::userStatistics($uid_in);
        $order = MerchantStatisticsOverviewService::orderStatistics($uid_in);
        //最新单笔收入
        $money = MerchantStatisticsOverviewService::todayData($uid_in);
        $coin = MerchantStatisticsOverviewService::weekCoinCount($uid_in);
        $merchant_name = MerchantCustomerService::getMerchantName();
        //var_dump($coin);die;
        $this->assign('merchant_name', $merchant_name);
        $this->assign('coin', $coin);
        $this->assign('money', $money);
        $this->assign('order', $order);
        $this->assign('user', $user);
        $this->assign('data', $data);
        return $this->fetch();
    }


    /**
     * 今日充值金币收入数据
     * @return false|string
     * @throws
     */
    public function todayIncome(){
        $condition = [
            'start_time' =>  strtotime(date("Y-m-d 00:00:00",time())),
            'end_time' =>  time(),
        ];
        $user_id = $this->getUserId();
        $res = MerchantStatisticsOverviewService::todayIncomeMoneyTotal($user_id);
        $data = [];
        if(!empty($res['recharge'])){
            foreach ($res['recharge'] as $item){
                $data['recharge'][] = $item['amount']/100;
            }
        }
        if(isset($data['recharge'])){
            $data['recharge'] = array_reverse($data['recharge']);
        }else{
            $data['recharge'] = [];
        }
        if(!empty($res['vip'])){
            foreach ($res['vip'] as $item){
                $data['vip'][] = $item['amount']/100;
            }
        }
        if(isset($data['vip'])){
            $data['vip'] = array_reverse($data['vip']);
        }else{
            $data['vip'] = [];
        }

        return json_encode(['data'=>$data,'code'=>200]);
    }

    /**
     * 消费统计
     * @throws
     */
    public function consumeCount(){
        $user_id = $this->getUserId();
        $res = MerchantStatisticsOverviewService::consumeCount($user_id);
        $data = [
            'week' => array_reverse(array_values($res['week'])),
            'video' => array_reverse(array_values($res[31])),
            'gift' => array_reverse(array_values($res[32])),
        ];
        //var_dump($data);die;
        return json_encode(['data'=>$data,'code'=>200]);
    }

    /**
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
