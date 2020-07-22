<?php
/**
 * User: coase
 * Date: 2018-10-29
 * Time: 18:18
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\PayModule;
use api\app\module\ConfigModule;
use api\app\module\pay\AlipayModule;
use api\app\module\pay\ApplepayModule;
use api\app\module\txyun\YuntongxinModule;

/**
 * #####支付的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.支付宝支付回调结果
 * 2.微信支付回调结果
 * ``````````````````
 */
class PayController extends RestBaseController
{
    /**
     * 充值金币预支付（给APP支付的第一步）
     *   创建本地支付订单
     *   获取支付渠道预支付参数
     */
    public function rechargePreparePay()
    {
        return false; // 现在用H5支付，该方法停用 2018-12-04
        try {
            $validate = new Validate([
                'coin' => 'require|integer', // 金币（1元=10金币）
                'pay_channel' => 'require|in:alipay,wxpay', // 支付渠道（支付宝，微信...）
            ]);

            $validate->message([
                'coin.require' => '请输入金币数!',
                'pay_channel.require' => '请输入支付渠道!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            # 创建充值订单
            $orderNo = PayModule::createRechargeOrder($userId, $param['coin'], $param['pay_channel']);
            if ($orderNo == false) {
                $this->error(PayModule::$errMessage);
            }

            # 充值预支付处理
            $res = PayModule::rechargePreparePay($orderNo, $param['pay_channel']);

            if ($res) {
                $this->success("OK", [
                    'pay_channel' => $param['pay_channel'],
                    'trade_no'    => $res['trade_no'],
                    'pay_data'    => $res['pay_data']
                ]);
            } else {
                $this->error("失败，" . PayModule::$errMessage);
            }

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 购买VIP预支付（给APP支付的第一步）
     *   创建本地支付订单
     *   获取支付渠道预支付参数
     */
    public function buyVipPreparePay()
    {
        return false; // 现在用H5支付，该方法停用 2018-12-04
        try {
            $validate = new Validate([
                'type' => 'require|in:1,2,3', // 1：月卡 2：季卡 3：年卡
                'pay_channel' => 'require|in:alipay,wxpay', // 支付渠道（支付宝，微信...）
            ]);

            $validate->message([
                'type.require' => '请输入购买类型!',
                'pay_channel.require' => '请输入支付渠道!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            # 创建充值订单
            $orderNo = PayModule::createBuyVipOrder($userId, $param['type'], $param['pay_channel']);
            if ($orderNo == false) {
                $this->error(PayModule::$errMessage);
            }

            # 购买vip预支付处理
            $res = PayModule::buyVipPreparePay($orderNo, $param['pay_channel']);

            if ($res) {
                $this->success("OK", [
                    'pay_channel' => $param['pay_channel'],
                    'trade_no'    => $res['trade_no'],
                    'pay_data'    => $res['pay_data']
                ]);
            } else {
                $this->error("失败，" . PayModule::$errMessage);
            }

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 创建本地充值支付订单（apple pay支付的第一步）
     */
    public function createRechargeOrder4Applypay()
    {
        try {
            $validate = new Validate([
                'money' => 'require', // 金额（单位元，1元=10金币）
            ]);

            $validate->message([
                'money.require' => '请输入金额!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $param['pay_channel'] = 'applepay';

            // 启动事务
            Db::startTrans();
            try{
                # 创建充值订单
                $orderNo = PayModule::createRechargeOrder($userId, $param['money']*100, $param['pay_channel']);
                if ($orderNo == false) {
                    $this->error(PayModule::$errMessage);
                }

                # 充值预支付处理
                $tradeInsert = [
                    'user_id' => $userId,
                    'class_id' => 1,
                    'order_no' => $orderNo,
                    'subject' => \dctool\Cgf::getCoinNickname().'充值',
                    'trade_no' => PayModule::createTradeNo(),
                    'trade_channel' => $param['pay_channel'],
                    'amount' => Db::name('recharge_order')->where('order_no', $orderNo)->value('amount'),
                    'status' => 0,
                    'create_time' => time(),
                ];
                Db::name('pay_trade')->insert($tradeInsert);

                Db::commit();
            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();

                throw new Exception('系统错误：' . $e->getMessage());
            }

            $this->success("OK", [
                'pay_channel' => $param['pay_channel'],
                'trade_no'    => $tradeInsert['trade_no'],
                'product_id'  => sprintf('product_recharge_%d', $param['money']*100)
            ]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 创建本地vip支付订单（apple pay支付的第一步）
     */
    public function createVipOrder4Applypay()
    {
        try {
            $validate = new Validate([
                'type' => 'require|in:1,2,3', // 1：月卡 2：季卡 3：年卡
            ]);

            $validate->message([
                'type.require' => '请输入购买类型!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();
            $param['pay_channel'] = 'applepay';

            // 启动事务
            Db::startTrans();
            try{
                # 创建充值订单
                $orderNo = PayModule::createBuyVipOrder($userId, $param['type'], $param['pay_channel']);
                if ($orderNo == false) {
                    $this->error(PayModule::$errMessage);
                }
                $amount = Db::name('vip_order')->where('order_no', $orderNo)->value('amount');

                # 充值预支付处理
                $tradeInsert = [
                    'user_id' => $userId,
                    'class_id' => 1,
                    'order_no' => $orderNo,
                    'subject' => '购买VIP',
                    'trade_no' => PayModule::createTradeNo(),
                    'trade_channel' => $param['pay_channel'],
                    'amount' => $amount,
                    'status' => 0,
                    'create_time' => time(),
                ];
                Db::name('pay_trade')->insert($tradeInsert);

                Db::commit();
            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();

                throw new Exception('系统错误：' . $e->getMessage());
            }

            $this->success("OK", [
                'pay_channel' => $param['pay_channel'],
                'trade_no'    => $tradeInsert['trade_no'],
                'product_id'  => sprintf('product_vip_%d', $amount)
            ]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 验证支付结果（apple pay支付的第二步）
     */
    public function verifyReceipt4Applypay()
    {
        try {
            // 调用参数日志记录
            Log::write(sprintf('%s，苹果支付结果验证，接收的数据：%s', __METHOD__, var_export($_POST,true)),'log');

            $validate = new Validate([
                'receipt_data' => 'require', // 客户端收到APPLE返回的receipt-data
                'trade_no' => 'require', // 交易号
            ]);

            $validate->message([
                'receipt_data.require' => '请输入receipt-data!',
                'trade_no.require' => '请输入交易号!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            # 请求苹果接口验证
            if (ApplepayModule::verifyReceipt($param['receipt_data']) !== true) {
                Log::write(sprintf('%s，苹果支付结果验证，receipt-data验证失败，接收的数据：%s', __METHOD__, var_export($_POST,true)),'error');
                $this->error('苹果支付验证失败');
            }

            $tradeRow = Db::name('pay_trade')->where('trade_no', $param['trade_no'])->find();

            # 完成订单
            if ($tradeRow['class_id'] == 1) {
                //充值订单
                $result = PayModule::finishRechargeOrder($param['trade_no']);
            } elseif ($tradeRow['class_id'] == 2) {
                //购买VIP订单
                $result = PayModule::finishBuyVipOrder($param['trade_no']);
            } else {
                throw new Exception('交易订单数据异常,单号:' . $param['trade_no']);
            }

            if (! $result) { // 订单成功
                Log::write(sprintf('%s，苹果支付结果验证，处理失败：，错误：%s，订单号：%s ', __METHOD__, PayModule::$errMessage, $param['trade_no']),'error');
            }

            // 发送系统消息
            if ($tradeRow['class_id'] == 1) {
                $sysNotice = sprintf('您有一笔充值订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            } else {
                $sysNotice = sprintf('您有一笔购买VIP订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            }
            $res = YuntongxinModule::pushSysNotice($tradeRow['user_id'], $sysNotice);
            if (!$res) {
                Log::write(sprintf('%s，苹果支付结果验证，发送通知信息失败：，错误：%s，订单号：%s ', __METHOD__, YuntongxinModule::$errMessage, $param['trade_no']),'error');
            }

            $this->success('OK');

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 支付宝支付回调结果
     */
    public function alipayCallback()
    {
        try {
            // 调用参数日志记录
            Log::write(sprintf('%s，支付宝支付回调，接收的数据：%s', __METHOD__, var_export($_POST,true)),'log');

            # 读取支付配置
            $config = PayModule::getPayInitConfig();

            # 签名验证
            if (! AlipayModule::callbackVerify($config['alipay'], $_POST)) { // 验证失败
                Log::write(sprintf('%s，支付宝支付回调，签名验证失败，接收的数据：%s', __METHOD__, var_export($_POST,true)),'error');
            }

            # 支付结果是否成功
            if ($_POST['trade_status'] !== 'TRADE_SUCCESS') {
                Log::write(sprintf('%s，支付宝支付回调，支付宝的参数trade_status不是交易成功：%s ', __METHOD__, var_export($_POST,true)),'error');
                $this->error('支付宝的状态参数异常');
            }

            # 获取参数
            $out_trade_no = $_POST['out_trade_no'];
            $total_amount = $_POST['total_amount'] * 100; //支付宝是元，这里转为分

            $tradeRow = Db::name('pay_trade')->where('trade_no', $out_trade_no)->find();

            if (! $tradeRow) {
                Log::write(sprintf('%s，支付宝支付回调，trade_no交易单号不存在：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('订单不存在');
            }

            if ($tradeRow['status'] >= 2) {
                Log::write(sprintf('%s，支付宝支付回调，trade_no交易已经处理完成了，不需要再回调了：%s ', __METHOD__, $out_trade_no),'error');
                echo "success";die;
            }

            if ($tradeRow['amount'] != $total_amount) {
                Log::write(sprintf('%s，支付宝支付回调，trade_no交易数据 amount 不一致：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('支付数据不一致');
            }

            if ($tradeRow['trade_channel'] != 'alipay') {
                Log::write(sprintf('%s，支付宝支付回调，trade_no交易数据 trade_channel 不等于alipay：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('支付数据不一致');
            }

            # 支付结果是否成功
            if ($_POST['trade_status'] !== 'TRADE_SUCCESS') {
                if ($_POST['trade_status'] == 'TRADE_CLOSED') { // 交易关闭
                    Db::name('pay_trade')->where('trade_no', $out_trade_no)->update(['status' => 10, 'extra' => json_encode($_POST), 'update_time' => time()]);
                    echo "success";die;
                }
                Log::write(sprintf('%s，支付宝支付回调，支付宝的参数trade_status不是交易成功：%s ', __METHOD__, var_export($_POST,true)),'error');
                $this->error('支付宝的状态参数异常');
            }

            # 完成订单
            if ($tradeRow['class_id'] == 1) {
                //充值订单
                $result = PayModule::finishRechargeOrder($out_trade_no, json_encode($_POST));
            } elseif ($tradeRow['class_id'] == 2) {
                //购买VIP订单
                $result = PayModule::finishBuyVipOrder($out_trade_no, json_encode($_POST));
            } else {
                throw new Exception('交易订单数据异常,单号:' . $out_trade_no);
            }

            if (! $result) { // 订单成功
                Log::write(sprintf('%s，支付宝支付回调，处理失败：，错误：%s，订单号：%s ', __METHOD__, PayModule::$errMessage, $out_trade_no),'error');
            }

            // 发送系统消息
            if ($tradeRow['class_id'] == 1) {
                $sysNotice = sprintf('您有一笔充值订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            } else {
                $sysNotice = sprintf('您有一笔购买VIP订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            }
            $res = YuntongxinModule::pushSysNotice($tradeRow['user_id'], $sysNotice);
            if (!$res) {
                Log::write(sprintf('%s，支付宝支付回调，发送通知信息失败：，错误：%s，订单号：%s ', __METHOD__, YuntongxinModule::$errMessage, $out_trade_no),'error');
            }

            echo "success";die;
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 微信支付回调结果
     */
    public function wxpayCallback()
    {
        try {
            // 调用参数日志记录
            Log::write(sprintf('%s，微信支付回调，接收的数据：%s', __METHOD__, var_export(file_get_contents('php://input'),true)),'log');

            # 初始化
            require_once(EXTEND_PATH.'paysdk/init.php');

            # 读取支付配置
            $config = PayModule::getPayInitConfig();

            # 实例支付对象
            $pay = new \Pay\Pay($config);
            $verify = $pay->driver('wechat')->gateway('mp')->verify(file_get_contents('php://input'));

            # 签名验证
            if (! $verify) { // 验证失败
                Log::write(sprintf('%s，微信支付回调，签名验证失败，接收的数据：%s', __METHOD__, var_export(file_get_contents('php://input'),true)),'error');
            }

            # 支付结果是否成功
            if ($verify['result_code'] !== 'SUCCESS') {
                Log::write(sprintf('%s，微信支付回调，微信的参数result_code不是交易成功：%s ', __METHOD__, var_export($verify,true)),'error');
                $this->error('微信的状态参数异常');
            }

            # 获取参数
            $out_trade_no = $verify['out_trade_no'];
            $total_amount = $verify['total_fee'];

            $tradeRow = Db::name('pay_trade')->where('trade_no', $out_trade_no)->find();

            if (! $tradeRow) {
                Log::write(sprintf('%s，微信支付回调，trade_no交易单号不存在：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('订单不存在');
            }

            if ($tradeRow['status'] >= 2) {
                Log::write(sprintf('%s，微信支付回调，trade_no交易已经处理完成了，不需要再回调了：%s ', __METHOD__, $out_trade_no),'error');
                header("Content-type:text/xml");
                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                die;
            }

            if ($tradeRow['amount'] != $total_amount) {
                Log::write(sprintf('%s，微信支付回调，trade_no交易数据 amount 不一致：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('支付数据不一致');
            }

            if ($tradeRow['trade_channel'] != 'wxpay') {
                Log::write(sprintf('%s，微信支付回调，trade_no交易数据 trade_channel 不等于wxpay：%s ', __METHOD__, $out_trade_no),'error');
                $this->error('支付数据不一致');
            }

            # 完成订单
            if ($tradeRow['class_id'] == 1) {
                //充值订单
                $result = PayModule::finishRechargeOrder($out_trade_no, json_encode($verify));
            } elseif ($tradeRow['class_id'] == 2) {
                //购买VIP订单
                $result = PayModule::finishBuyVipOrder($out_trade_no, json_encode($verify));
            } else {
                throw new Exception('交易订单数据异常,单号:' . $out_trade_no);
            }

            if (! $result) {
                Log::write(sprintf('%s，微信支付回调，处理失败：%s，订单号：%s ', __METHOD__, PayModule::$errMessage, $out_trade_no),'error');
            }

            // 发送系统消息
            if ($tradeRow['class_id'] == 1) {
                $sysNotice = sprintf('您有一笔充值订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            } else {
                $sysNotice = sprintf('您有一笔购买VIP订单已经完成，支付金额：%.2f元', $tradeRow['amount'] / 100);
            }
            $res = YuntongxinModule::pushSysNotice($tradeRow['user_id'], $sysNotice);
            if (!$res) {
                Log::write(sprintf('%s，支付宝支付回调，发送通知信息失败：，错误：%s，订单号：%s ', __METHOD__, YuntongxinModule::$errMessage, $out_trade_no),'error');
            }

            header("Content-type:text/xml");
            echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";die;
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
