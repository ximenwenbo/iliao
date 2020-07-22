<?php
/**
 * User: coase
 * Date: 2018-11-01
 * Time: 14:14
 */
namespace api\apph5\controller;

use api\app\module\PayModule;
use api\app\module\VipModule;
use api\apph5\model\UserModel;
use cmf\controller\HomeBaseController;
use think\Db;
use think\Exception;
use think\Request;

/**
 * #####支付H5模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.支付页面
 * 2.支付完成页面
 * ``````````````````
 */
class PayController extends HomeBaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        // 响应头设置 通过设置header来跨域
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:*');
        header('Access-Control-Allow-Credentials:false');
    }

    /**
     * 支付方式页面
     * @method POST
     * @requestParam $money double
     * @requestParam $scene int 1.充值金币 2.充值vip
     * @requestParam $token string
     */
    public function payPage()
    {
        $domain = $this->request->domain();
        //获取post参数
        $money = $this->request->post('money');
        $name = $this->request->post('scene');  //场景:1充值金币 2充值vip
        $type = $this->request->post('type');  //0无 1月卡 2季卡 3年卡
        $token = $this->request->post('token');
        $android = $this->request->post('android');
        if (!in_array($type,[0,1,2,3]) || !in_array($name,[1,2]) || empty($token)) {
            return json_encode(['code'=>0,'msg'=>'参数有误']);
        }

        $scene = [1=>'充值'.\dctool\Cgf::getCoinNickname(), 2 => '充值vip'];
        $type_name = [0=> '', 1=>'(月卡)', 2 => '(季卡)',3=>'(年卡)'];
        $channel = isset(cmf_get_option('pay_conf')['channel']) ? cmf_get_option('pay_conf')['channel'] : [];

        $this->assign('domain', $domain);
        $this->assign('money', floatval($money));
        $this->assign('scene', $scene[$name]);
        $this->assign('token', $token);
        $this->assign('type_name', $type_name[$type]);
        $this->assign('type', $type);
        $this->assign('name', $name);
        $this->assign('channel', $channel);
        if($android){
            return $this->fetch('pay_page');
        }else{
//            echo json_encode(['code'=>1,'msg'=>'对不起，该页面去流浪了！']);die; // todo 为了通过apple store的变态审核 at 2019-03-11 by Coase
            $h5 = base64_encode($this->fetch('pay_page'));
            echo json_encode(['code'=>1,'msg'=>'OK', 'data'=> ['h5'=>$h5]]);exit;
        }

    }


    /**
     * 支付业务
     * @author zjy
     */
    public function payPageAjax(){
        if($this->request->isPost()){
            $param = $this->request->param();
            try{
                //验证数据
                if(!isset($param['pay_money']) || empty($param['pay_money'])){
                    return json_encode(['code'=>100033, 'msg'=> '金额有误']);
                }
                if(!isset($param['pay_check']) || !in_array($param['pay_check'],['alipay','wxpay'])){
                    return json_encode(['code'=>100033, 'msg'=> '支付方式有误']);
                }

                if(!isset($param['token']) || empty($param['token'])){
                    return json_encode(['code'=>100033, 'msg'=> '用户身份验证失败']);
                }

                if(!isset($param['type']) || !is_numeric($param['type'])){
                    return json_encode(['code'=>100033, 'msg'=> '月卡类型有误']);
                }

                //获取用户id
                $model = new UserModel();
                $user_id = $model->getUserInfo($param['token']);
                if(isset($user_id['code'])){
                    return json_encode(['code'=>1100,'msg'=>$user_id['msg']]);
                }
                $param['pay_money'] = $param['pay_money']*100;
                //支付场景 1:充值金币 2.充值vip=>a.月卡 b.季卡 c.年卡
                switch ($param['type'])
                {
                    case 0:
                        # 充值预支付处理
                        $order_numb = PayModule::createRechargeOrder($user_id,$param['pay_money'],$param['pay_check']);
                        $res = PayModule::rechargePreparePay($order_numb,$param['pay_check']);
                        break;
                    case 1:
                    case 2:
                    case 3:
                        # 购买vip支付处理
                        $order_numb = PayModule::createBuyVipOrder($user_id,$param['type'], $param['pay_check']);
                        $res = PayModule::buyVipPreparePay($order_numb,$param['pay_check']);
                        break;
                    default :
                        $res = '';
                        $order_numb='';
                        break;
                }
                if(!empty($order_numb)){
                    $trade_no = Db::name('pay_trade')->where(['order_no'=>$order_numb])->value('trade_no');
                }else{
                    $trade_no='';
                }

                if ($res) {
                    return json_encode(['code'=>200,'msg'=>'支付成功','data'=>$res, 'pay_check'=>$param['pay_check'],'trade_no'=>$trade_no]);
                } else {
                    return json_encode(['code'=>30001,'msg'=>PayModule::$errMessage]);
                }
            }catch (Exception $e) {
                return json_encode(['code'=>30001,'msg'=>$e->getMessage()]);
            }


        }
    }

    /**
     * 支付完成页面
     * @throws
     */
    public function payComplete()
    {

        $domain = $this->request->domain();
        $trade_no = $this->request->param('our_trade_no','');
        $this->assign('domain', $domain);
        if (empty($trade_no)){
            return $this->fetch('pay_fail');
        }else{
            $pay_info = Db::name('pay_trade')->where("trade_no='{$trade_no}'")->value('status');
            if ($pay_info==2){
                $this->assign('pay_info', $pay_info);
                return $this->fetch('pay_complete');
            }else{
                return $this->fetch('pay_fail');
            }
        }

    }
}
