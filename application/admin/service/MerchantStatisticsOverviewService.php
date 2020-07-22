<?php
namespace app\admin\service;

use app\admin\model\UserCoinModel;
use think\Db;
use think\Exception;

class MerchantStatisticsOverviewService extends BaseService
{
    /**
     * 注册人数
     * @param $day int 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function memberTotal($day,$user_id)
    {
        if($day==1){
            $today_start = strtotime(date("Y-m-d 00:00:00",time()));
            $today_end = time();
        }elseif($day==0){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        }elseif ($day==2){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-7 day")));
            $today_end = time();
        }else{
            $today_start = strtotime(date("Y-m-01 00:00:00",time()));
            $today_end = strtotime(date("Y-m-01 00:00:00",strtotime("+1 month"))) - 1;
        }
        $total = Db::name('user')->where("create_time >= $today_start and create_time <= $today_end and user_type = 2 and id in($user_id)")->count();
        //$total = Db::name('user')->where("create_time >= $today_start and create_time <= $today_end and user_type = 2 and id in($user_id)")->getLastSql();
        return $total;

    }

    /**
     * 充值金币金额
     * @param $day int 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function moneyTotal($day,$user_id)
    {
        if($day==1){
            $today_start = strtotime(date("Y-m-d 00:00:00",time()));
            $today_end = time();
        }elseif($day==0){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        }elseif ($day==2){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-7 day")));
            $today_end = time();
        }else{
            $today_start = strtotime(date("Y-m-01 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-01 00:00:00",strtotime("+1 month"))) - 1;
        }
        $total = Db::name('pay_trade')
                ->where("status = 2 and class_id = 1 
                                and create_time >= $today_start and create_time <= $today_end and user_id in($user_id)")
                ->sum('amount');
        return $total;

    }

    /**
     * 充值vip金额
     * @param $day int 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function VipTotal($day,$user_id)
    {
        if($day==1){
            $today_start = strtotime(date("Y-m-d 00:00:00",time()));
            $today_end = time();
        }elseif($day==0){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        }elseif ($day==2){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-7 day")));
            $today_end = time();
        }else{
            $today_start = strtotime(date("Y-m-01 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-01 00:00:00",strtotime("+1 month"))) - 1;
        }
        $total = Db::name('pay_trade')
                ->where("status = 2 and class_id = 2 
                                    and create_time >= $today_start and create_time <= $today_end and user_id in($user_id)")
                ->sum('amount');
        return $total;

    }

    /**
     * 消费金额
     * @param $day int 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function monetaryTotal($day,$user_id)
    {
        if($day==1){
            $today_start = strtotime(date("Y-m-d 00:00:00",time()));
            $today_end = time();
        }elseif($day==0){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")));
        }elseif ($day==2){
            $today_start = strtotime(date("Y-m-d 00:00:00",strtotime("-7 day")));
            $today_end = time();
        }else{
            $today_start = strtotime(date("Y-m-01 00:00:00",strtotime("-1 day")));
            $today_end = strtotime(date("Y-m-01 00:00:00",strtotime("+1 month"))) - 1;
        }
        $total = Db::name('user_coin_record')->where("class_id =3 and change_type = 2 and create_time >= $today_start and create_time <= $today_end and user_id in($user_id)")->sum('change_coin');
        return $total;

    }

    /**
     * 用户总数
     */
    public static function userStatistics($user_id){
        $user_number = Db::name('user')->where("user_type=2 and user_status=1 and id in($user_id)")->count();
        $user_sex_man = Db::name('user')->where("user_type=2 and user_status=1 and sex=1 and id in($user_id)")->count();
        $user_sex_woman = Db::name('user')->where("user_type=2 and user_status=1 and sex=2 and id in($user_id)")->count();
        //$user_sex_bm = Db::name('user')->where(['user_type'=>2,'user_status'=>1,'sex'=>3])->count();
        $time = time();
        $vip = Db::name('user')->where("vip_expire_time > $time and user_type = 2 and user_status = 1 and id in($user_id)")->count();
        $daren = Db::name('user')->where("daren_status = 2 and user_type = 2 and user_status = 1 and id in($user_id)")->count();
        $data = [
            'member' => $user_number,
            'sex' => ['man'=>$user_sex_man, 'woman'=>$user_sex_woman],
            'vip' => $vip,
            'daren' => $daren,
        ];
        return $data;
    }

    /**
     * 订单统计
     */
    public static function orderStatistics($user_id){
        $order_recharge_number = Db::name('recharge_order')->where("status=2 and user_id in($user_id)")->count();
        $order_recharge_money = Db::name('recharge_order')->where("status=2 and user_id in($user_id)")->sum('amount')/100;
        $order_vip_number = Db::name('vip_order')->where("status=2 and user_id in($user_id)")->count();
        $order_vip_money = Db::name('vip_order')->where("status=2 and user_id in($user_id)")->sum('amount')/100;
        $data = [
            'recharge_number' => $order_recharge_number,
            'recharge_money' => $order_recharge_money,
            'vip_number' => $order_vip_number,
            'vip_money' => $order_vip_money,
            'total_number' => $order_recharge_number + $order_vip_number,
            'total_money' => $order_recharge_money + $order_vip_money,
        ];

        return $data;
    }


    /**
     * 今日收入最新订单金额和总收入
     * @throws
     */
    public static function todayData($user_id){
        $start_time = strtotime(date("Y-m-d 00:00:00",time()));
        $end_time = time();
        $coin = Db::name('recharge_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->order('id desc')->limit(0,1)->select()->toArray();
        $vip = Db::name('vip_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->order('id desc')->limit(0,1)->select()->toArray();
        $coin_today_total = Db::name('recharge_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->sum('amount');
        $vip_today_total = Db::name('vip_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->sum('amount');
        $data = [
            'coin' => isset($coin[0]['amount']) ? $coin[0]['amount']/100 : 0,
            'vip' => isset($vip[0]['amount']) ? $vip[0]['amount']/100 : 0,
            'coin_amount_total' => empty($coin_today_total) ? 0 : $coin_today_total/100,
            'vip_amount_total' => empty($vip_today_total) ? 0 : $vip_today_total/100
        ];
        return $data;
    }

    /**
     * 今日充值前10订单金额
     * @throws
     */
    public static function todayIncomeMoneyTotal($user_id){
        $start_time = strtotime(date("Y-m-d 00:00:00",time()));
        $end_time = time();
        $recharge = Db::name('recharge_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->field('amount')->order('id desc')->limit(0,10)->select()->toArray();
        $vip = Db::name('vip_order')->where("status = 2 and create_time >= {$start_time} and create_time <= {$end_time} and user_id in($user_id)")->field('amount')->order('id desc')->limit(0,10)->select()->toArray();
        $data = [
            'recharge' =>$recharge,
            'vip' => $vip
        ];
        return $data;
    }

    /**
     * 消费统计
     */
    public static function consumeCount($user_id){
        $week = ["周日","周一","周二","周三","周四","周五","周六"];
        foreach ($week as $key=>$item){
            $str_time = strtotime("-{$key} day");
            $start_time = strtotime(date("Y-m-d 00:00:00",$str_time));
            $end_time = strtotime(date("Y-m-d 23:59:59",$str_time));
            $week_day = date("w",$str_time);
            $new_week['week'][$key] = $week[$week_day];
            $model = new UserCoinModel();
            $where="class_id = 3 and create_time <= {$end_time} and create_time >= {$start_time} and change_class_id = 31 and user_id in($user_id)";
            $new_week[31][$key] = $model->fieldSum($where,'change_coin');
            $where="class_id = 3 and create_time <= {$end_time} and create_time >= {$start_time} and change_class_id = 32 and user_id in($user_id)";
            $new_week[32][$key] = $model->fieldSum($where,'change_coin');
        }
        return $new_week;
    }

    /**
     * 一周金币消费
     */
    public static function weekCoinCount($user_id){
        $start_time = strtotime(date("Y-m-d H:i:s",strtotime('-7 day')));
        $end_time = time();
        $coin_total = Db::name('user_coin_record')
            ->where("class_id = 3 and create_time <= {$end_time} and create_time >= {$start_time} and user_id in($user_id)")
            ->sum('change_coin');
        $coin_video = Db::name('user_coin_record')
            ->where("class_id = 3 and create_time <= {$end_time} and create_time >= {$start_time} and change_class_id = 31 and user_id in($user_id)")
            ->sum('change_coin');
        $coin_gift = Db::name('user_coin_record')
            ->where("class_id = 3 and create_time <= {$end_time} and create_time >= {$start_time} and change_class_id = 32 and user_id in($user_id)")
            ->sum('change_coin');
        $data = [
            'coin_total' => $coin_total,
            'coin_video' => $coin_video,
            'coin_gift' => $coin_gift,
        ];
        return $data;
    }
}