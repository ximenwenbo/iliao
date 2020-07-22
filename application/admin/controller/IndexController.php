<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\service\IndexService;
use app\admin\service\UserCenterService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;
use think\Exception;
use think\Session;

class IndexController extends AdminBaseController
{

    public function _initialize()
    {
        $adminSettings = cmf_get_option('admin_settings');
        if (empty($adminSettings['admin_password']) || $this->request->path() == $adminSettings['admin_password']) {
            $adminId = cmf_get_current_admin_id();
            if (empty($adminId)) {
                session("__LOGIN_BY_CMF_ADMIN_PW__", 1);//设置后台登录加密码
            }
        }

        parent::_initialize();
    }

    /**
     * 后台框架
     * @throws Exception
     */
    public function index()
    {
        $content = hook_one('admin_index_index_view');

        if (!empty($content)) {
            return $content;
        }

        $adminMenuModel = new AdminMenuModel();
        $menus  = cache('admin_menus_' . cmf_get_current_admin_id(), '', null, 'admin_menus');
        $menus = 0;
        if (empty($menus)) {
            $menus = $adminMenuModel->menuTree();
            //cache('admin_menus_' . cmf_get_current_admin_id(), $menus, null, 'admin_menus');
        }

        $this->assign("submenus", $menus);
        //var_dump($menus);die;

        $result = Db::name('AdminMenu')->order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $menusTmp = array();
        foreach ($result as $item){
            //去掉/ _ 全部小写。作为索引。
            $indexTmp = $item['app'].$item['controller'].$item['action'];
            $indexTmp = preg_replace("/[\\/|_]/","",$indexTmp);
            $indexTmp = strtolower($indexTmp);
            $menusTmp[$indexTmp] = $item;
        }
        $this->assign("menus_js_var",json_encode($menusTmp));

        $messageList = UserCenterService::messageList(['read_flag'=>0]);
        if(!isset($messageList['data'])){
            $messageList['data'] = [];
        }

        //var_dump($messageList);die;
        $this->assign('msgList', $messageList['data']);
        return $this->fetch();
    }

    /**
     * 首页统计
     * @return mixed
     * @throws
     */
    public function home()
    {
        $admin_id = Session::get('ADMIN_ID');
        //商家客服跳转至商户统计首页
        $is_kefu = Db::name('merchant_customer')->where("user_id = {$admin_id} and user_id != 1")->value('id');
        if($is_kefu){
            $this->redirect("MerchantStatisticsOverview/index");
        }
        $data = [];
        $today = ['昨天','今天','本周','本月'];
        foreach ($today as $key=>$item){
            $filed =  [
                'day' => $item,
                'member' => IndexService::memberTotal($key), //注册人数
                'money' => IndexService::moneyTotal($key)/100, //充值金币金额
                'vip' => IndexService::VipTotal($key)/100,  //充值vip金额
                'monetary' => IndexService::monetaryTotal($key)     //消费金额
            ];
            array_push($data,$filed);
        }
        //用户总数
        $user = IndexService::userStatistics();
        //订单统计
        $order = IndexService::orderStatistics();
        //最新单笔收入
        $money = IndexService::todayData();
        //一周消费金币
        $coin = IndexService::weekCoinCount();

        //网红榜 守护订单表 守护天数最多从多到少取3条
        $red_list = Db::name('watch_order')
                        ->alias('w')
                        ->join('user u','u.id = w.receive_uid')
                        ->where("w.status = 1 and u.user_type = 2 and u.user_status = 1")
                        ->field('sum(w.day_time) as w_day, w.receive_uid,u.user_nickname,u.avatar')
                        ->group('w.receive_uid')
                        ->order('w_day desc')
                        ->limit(0,3)
                        ->select()->toArray();

        //富豪榜 消费最多从多到少取3条
        $subQuery = Db::name('user_coin_record')
            ->alias('r')
            ->join('user u', 'u.id = r.user_id')
            ->where('r.class_id', 3)
            ->group('r.user_id')
            ->field('SUM(r.change_coin) sum_coin,r.user_id,u.user_nickname,u.avatar,u.sex,u.city_name,u.vip_expire_time')
            ->order('sum_coin desc')
            ->buildSql();

        // 获取排名靠前的数据
        $sql = Db::table($subQuery . ' as lis')
            ->join('user_follow f', 'f.be_user_id = lis.user_id ', 'LEFT')
            ->order('lis.sum_coin', 'desc')
            ->field('distinct `lis`.user_id,`lis`.sum_coin,`lis`.user_nickname,`lis`.avatar,`lis`.sex,`lis`.city_name,`lis`.vip_expire_time')
            ->limit(0,3);
        $rich_list = $sql->select()->toArray();
        /*$rich_list = Db::name('user_coin_record')
                    ->alias('c')
                    ->join('user u','u.id = c.user_id')
                    ->where("u.user_type = 2 and u.user_status = 1")
                    ->field('sum(c.change_coin) as c_coin, c.user_id,u.user_nickname,u.avatar')
                    ->group('c.user_id')
                    ->order('c_coin desc')
                    ->limit(0,3)
                    ->select()->toArray();*/
        //消费数据
        $consume = [
            'gift' =>  Db::name("user_coin_record")->where("class_id = 3 and change_class_id = 32")->sum('change_coin'), //礼物支付
            'guard' =>  Db::name("user_coin_record")->where("class_id = 3 and change_class_id = 34")->sum('change_coin'), //守护支付
            'live' =>  Db::name("user_coin_record")->where("class_id = 3 and change_class_id = 33")->sum('change_coin'), //直播间门票支付
            'audio' =>  Db::name("user_coin_record")->where("class_id = 3 and change_class_id = 31")->sum('change_coin'), //音视频聊天支付
            'total' =>  Db::name("user_coin_record")->where("class_id = 3")->sum('change_coin'), //音视频聊天支付
        ];


        $this->assign('consume', $consume);
        $this->assign('red_list', $red_list);
        $this->assign('rich_list', $rich_list);
        $this->assign('coin', $coin);
        $this->assign('money', $money);
        $this->assign('order', $order);
        $this->assign('user', $user);
        $this->assign('data', $data);
        return $this->fetch();
    }

    /**
     * 头部导航栏
     * @return mixed
     * @throws
     */
    public function headerNav(){
        $tab = $this->request->get('tab','message');
        $pageIndex = $this->request->get('pageIndex',0);
        $result = UserCenterService::messageList(['offset'=>$pageIndex]);
        $user_info = UserService::ToInfo(['id'=>cmf_get_current_admin_id()]);

        //登录、消息、日志的数量
        $admin_id = cmf_get_current_admin_id();
        $user['login_num'] = Db::name('admin_log_record')->where("admin_id = $admin_id and type = 1 and status = 1")->count();
        $user['message_num'] = Db::name('user_message')->where("receive_id = $admin_id and type = 1 and status = 1")->count();
        $user['log_num'] = Db::name('admin_log_record')->where("admin_id = $admin_id and status = 1")->count();

        //var_dump($user);die;
        $this->assign('user', $user);
        $this->assign('user_info', $user_info);
        $this->assign('data', $result);
        $this->assign('pageTotal', intval(count($result['data'])/20));
        $this->assign('tab', $tab);
        $this->assign('pageIndex', $pageIndex);
        return $this->fetch('account');
    }

    /**
     * 日志列表
     * @return false|string
     */
    public function logsAjax(){
        $params = $this->request->param();
        $condition = [
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => 'id',
            'sortType' => 'desc',
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = IndexService::LogsList($condition);
        //var_dump($domain=$this->request->domain());die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                //定义操作类型
                $type = ['无','登陆','登出','修改密码','修改会员信息','修改机器人信息'];
                $filed =  [
                    'id' => $val['id'],
                    'type' => $type[$val['type']],
                    'user_nickname' => $val['user_nickname'],
                    'url' => $val['url'],
                    'params' => $val['params'],
                    "create_time"=> date("Y-m-d H:i:s",$val['create_time']),
                    "admin_ip"=> $val['admin_ip'],
                    "remark"=> $val['remark'],
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
            "data"=> $params//表单参数
        ]);
    }

    /**
     * 今日充值金币收入数据
     * @return false|string
     */
    public function todayIncome(){
        $condition = [
          'start_time' =>  strtotime(date("Y-m-d 00:00:00",time())),
          'end_time' =>  time(),
        ];
        $res = IndexService::todayIncomeMoneyTotal();
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
     */
    public function consumeCount(){
        $res = IndexService::consumeCount();
        $data = [
            'week' => array_reverse(array_values($res['week'])),
            'video' => array_reverse(array_values($res[31])),
            'gift' => array_reverse(array_values($res[32])),
        ];
        //var_dump($data);die;
        return json_encode(['data'=>$data,'code'=>200]);
    }


    /**
     * 注册人数使用设备类型数
     */
    public function getUserDevice(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            $total = Db::name('user')->where("user_status = 1 and user_type=2")->count();
            $iphone = Db::name('user')->where("device_brand in('iphone','苹果') and user_type = 2 and user_status = 1")->count();
            $android = $total - $iphone;
            return json_encode(['code' => 200, 'iphone' => $iphone,'android' => $android]);
        }else{
            $this->error('错误访问类型');
        }
    }

    /**
     * 注册人数性别占比
     */
    public function getUserReg(){
        if($this->request->isAjax()){
            $man = Db::name('user')->where("user_status = 1 and user_type=2 and sex = 1")->count();
            $woman = Db::name('user')->where("user_type = 2 and user_status = 1 and sex = 2")->count();
            $secrecy = Db::name('user')->where("user_type = 2 and user_status = 1 and sex = 0")->count();
            $data = [
                'man' => $man,
                'woman' => $woman,
                'secrecy' => $secrecy,
            ];
            return json_encode(['code' => 200, 'data' => $data]);
        }else{
            $this->error('错误访问类型');
        }
    }

    /**
     * 获取消息条数
     */
    public function getMessageNumber(){
        if($this->request->isAjax()){
            $messageList = UserCenterService::messageList(['read_flag'=>0]);
            if(!isset($messageList['data'])){
                $messageList['data'] = [];
            }
            if(empty($messageList)){
                return json_encode(['code' => 0]);
            }else{
                return json_encode(['code' => 200, 'data'=>$messageList['data'],'num' => $messageList['total']]);
            }
        }
    }
}
