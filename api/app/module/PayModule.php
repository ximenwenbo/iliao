<?php
/**
 * 支付功能模块
 */
namespace api\app\module;

use think\Db;
use think\Exception;
use api\app\module\pay\AlipayModule;

class PayModule extends BaseModule
{
    /**
     * 充值预支付（充值第一步）
     * @param $orderNo
     * @param $pay_channel
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rechargePreparePay($orderNo, $pay_channel)
    {
        $orderRow = Db::name('recharge_order')->where('order_no', $orderNo)->find();
        if ($orderRow['status'] >= 2) {
            self::exceptionError('订单已经结束了', -1018);
            return false;
        }

        $tradeRow = Db::name('pay_trade')->where('order_no', $orderNo)->find();
        if ($tradeRow) {
            if ($tradeRow['status'] >= 2) {
                self::exceptionError('订单已经结束了', -1019);
                return false;
            }

            return self::unifiedPay($tradeRow['trade_no']);
        }

        # 新增交易订单
        $tradeNo = self::createTradeNo();
        $tradeInsert = [
            'user_id' => $orderRow['user_id'],
            'class_id' => 1,
            'order_no' => $orderRow['order_no'],
            'subject' => \dctool\Cgf::getCoinNickname().'充值',
            'trade_no' => $tradeNo,
            'trade_channel' => $pay_channel,
            'amount' => $orderRow['amount'],
            'status' => 0,
            'extra' => '',
            'create_time' => time(),
        ];
        Db::name('pay_trade')->insertGetId($tradeInsert);

        return self::unifiedPay($tradeNo);
    }

    /**
     * 购买vip预支付（充值第一步）
     * @param $orderNo
     * @param $pay_channel
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function buyVipPreparePay($orderNo, $pay_channel)
    {
        $orderRow = Db::name('vip_order')->where('order_no', $orderNo)->find();

        # 新增交易订单
        $tradeNo = self::createTradeNo();
        $tradeInsert = [
            'user_id' => $orderRow['user_id'],
            'class_id' => 2, // 支付种类（1:充值金币 2:购买VIP）
            'order_no' => $orderRow['order_no'],
            'subject' => '购买VIP',
            'trade_no' => $tradeNo,
            'trade_channel' => $pay_channel,
            'amount' => $orderRow['amount'],
            'status' => 0,
            'extra' => '',
            'create_time' => time(),
        ];
        Db::name('pay_trade')->insertGetId($tradeInsert);

        return self::unifiedPay($tradeNo);
    }

    /**
     * 生成充值订单
     *
     * @param $userId
     * @param int $money 金额(单位分)
     * @param $pay_channel
     * @param array $ext 扩展
     * @return int|string
     */
    public static function createRechargeOrder($userId, $money, $pay_channel = '', $ext = [])
    {
        # 把金币换算成钱，单位分
        $amount = $money;
        $coin = ConfigModule::money2coin($amount);
        $time = time();

        $addOrder = [
            'order_no' => self::createOrderNo(),
            'user_id' => $userId,
            'pay_channel' => $pay_channel,
            'amount' => $amount,
            'coin' => $coin,
            'subject' => '充值'.\dctool\Cgf::getCoinNickname(),
            'create_time' => $time,
        ];

        $result = Db::name('recharge_order')->insert($addOrder);
        if ($result) {
            return $addOrder['order_no'];
        } else {
            self::exceptionError('生成充值订单失败', -1011);
            return false;
        }
    }

    /**
     * 生成购买VIP订单
     *
     * @param $userId
     * @param int $type 类型（1：月卡 2：季卡 3：年卡）
     * @param $pay_channel
     * @param array $ext 扩展
     * @return int|string
     */
    public static function createBuyVipOrder($userId, $type, $pay_channel = '', $ext = [])
    {
        $vipList = VipModule::getVipTypeList();
        # 根据vip类型获取支付金额，单位分
        $amount = $vipList[$type]['money'] * 100;
        $time = time();

        $addOrder = [
            'order_no' => self::createOrderNo(),
            'user_id' => $userId,
            'pay_channel' => $pay_channel,
            'type' => $type,
            'amount' => $amount,
            'subject' => '购买VIP',
            'create_time' => $time,
        ];

        $result = Db::name('vip_order')->insert($addOrder);
        if ($result) {
            return $addOrder['order_no'];
        } else {
            self::exceptionError('生成vip购买订单失败', -1011);
            return false;
        }
    }

    /**
     * 统一下单 (微信使用paysdk，支付宝使用alipaysdk)
     *   根据支付方式，获取跳转链接，并返回
     * @param string $tradeNo 交易单号
     * @return bool|mixed
     */
    private static function unifiedPay($tradeNo)
    {
        $payRow = Db::name('pay_trade')->where('trade_no', $tradeNo)->find();

        # 初始化支付SDK
        require_once(EXTEND_PATH.'paysdk/init.php');

        # 读取支付配置
        $config = self::getPayInitConfig();

        if ($payRow['trade_channel'] == 'alipay') { // 支付宝H5支付

//            // 支付参数
//            $options = [
//                'out_trade_no' => $payRow['trade_no'], // 商户订单号
//                'total_amount' => strval(sprintf('%.2f', $payRow['amount']/100)), // 支付金额 单位：元
//                'body'         => $payRow['subject'],
//                'subject'      => $payRow['subject'], // 支付订单描述
//                'notify_url'   => url('/app/pay/alipayCallback', '', '', true), // 定义通知URL
//                'return_url'   => url('/apph5/pay/paycomplete', ['trade_no' => $tradeNo], '', true), // 支付完成返回URL
//            ];
//
//            // 调用支付宝支付模块
//            try {
//                $orderStr = AlipayModule::unifiedPay($config['alipay'], $options);
//                $result['trade_no'] = $tradeNo;
//                $result['pay_data'] = $orderStr;
//
//                return $result;
//            } catch (\Exception $e) {
//                self::exceptionError('预支付失败，' . $e->getMessage(), -1055);
//                return false;
//            }

            // 请求参数
            $options = [
                'out_trade_no' => $payRow['trade_no'], // 商户订单号
                'total_amount' => strval(sprintf('%.2f', $payRow['amount']/100)), // 支付金额 单位：元
                'body'         => $payRow['subject'],
                'subject'      => $payRow['subject'], // 支付订单描述
            ];

            // 公共参数
            $config['alipay']['notify_url'] = url('/app/pay/alipayCallback', '', '', true); // 定义通知URL
            $config['alipay']['return_url'] = url('/apph5/pay/paycomplete', ['our_trade_no' => $tradeNo], '', true); // todo 支付完成返回URL

            // 实例支付对象
            $pay = new \Pay\Pay($config);
            try {
                $payStr = $pay->driver('alipay')->gateway('wap')->apply($options);
                $result['trade_no'] = $tradeNo;
                $result['pay_data'] = $payStr;

                return $result;
            } catch (Exception $e) {
                self::exceptionError('预支付失败，' . $e->getMessage(), -1055);
                return false;
            }

        } elseif ($payRow['trade_channel'] == 'wxpay') { // 微信H5支付
            $driver = 'wechat';
            $gateway = 'wap';

            // 支付参数
            $options = [
                'out_trade_no'     => $payRow['trade_no'], // 订单号
                'total_fee'        => $payRow['amount'], // 订单金额 单位：分
                'body'             => $payRow['subject'], // 订单描述
                'spbill_create_ip' => get_client_ip(), // 支付人的 IP
                'notify_url'       => url('/app/pay/wxpayCallback', '', '', true), // 定义通知URL
            ];

            // 实例支付对象
            $pay = new \Pay\Pay($config);
            try {
                $result['trade_no'] = $tradeNo;
                $MWEBURL = $pay->driver($driver)->gateway($gateway)->apply($options);
                $result['pay_data'] = $MWEBURL . url('/apph5/pay/paycomplete', ['our_trade_no' => $tradeNo], '', true);
                return $result;
            } catch (\Exception $e) {
                self::exceptionError('预支付失败，' . $e->getMessage(), -1055);
                return false;
            }

        } else {
            self::exceptionError('未知支付渠道，', -1053);
            return false;
        }
    }

    /**
     * 完成充值订单
     * 增加用户余额
     * 增加一条余额变更记录
     */
    public static function finishRechargeOrder($tradeNo, $extraStr = '')
    {
        $tradeRow = Db::name('pay_trade')->where('trade_no', $tradeNo)->find();
        if (! $tradeRow) {
            self::exceptionError('交易订单不存在:' . $tradeNo, -1041);
            return false;
        }

        $orderRow = Db::name('recharge_order')->where('order_no', $tradeRow['order_no'])->find();
        if (! $orderRow) {
            self::exceptionError('充值订单不存在', -1051);
            return false;
        }
        if ($orderRow['status'] >= 2) {
            self::exceptionError('充值订单已经完成了', -1052);
            return false;
        }

        $currentTime = time();
        // 启动事务
        Db::startTrans();
        try{
            // 变更订单状态
            Db::name('pay_trade')->where('trade_no', $tradeNo)->update([
                'status' => 2,
                'finish_time' => $currentTime,
                'extra' => $extraStr
            ]);

            Db::name('recharge_order')->where('order_no', $orderRow['order_no'])->update(
                ['status' => 2, 'pay_channel' => $tradeRow['trade_channel'], 'finish_time' => $currentTime]
            );

            // 增加用户金币
            Db::name('user')->where('id', $orderRow['user_id'])->setInc('coin', $orderRow['coin']);

            // 获取变更后的金币，不可提现
            $coin = Db::name('user')->where('id', $orderRow['user_id'])->value('coin');

            // 新增积分变更记录
            $insertCoin = [
                'user_id' => $orderRow['user_id'],
                'change_type' => 1, // 变动方向 1增加 2减少
                'coin_type' => 2, // 1:可提现 2:不可提现
                'class_id' => 1, // 类别 1:充值
                'change_class_id' => 1, // 变动类别 1:充值
                'change_coin' => $orderRow['coin'],
                'coin' => $coin,
                'change_data_id' => $orderRow['id'],
                'change_subject' => '充值'.\dctool\Cgf::getCoinNickname(),
                'create_time' => $currentTime
            ];
            Db::name('user_coin_record')->insert($insertCoin);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            self::exceptionError('系统错误：' . $e->getMessage(), -9999);
            return false;
        }

        return true;
    }

    /**
     * 完成购买VIP订单
     *   增加用户VIP到期时间
     *
     */
    public static function finishBuyVipOrder($tradeNo, $extraStr = '')
    {
        $tradeRow = Db::name('pay_trade')->where('trade_no', $tradeNo)->find();
        if (! $tradeRow) {
            self::exceptionError('交易订单不存在:' . $tradeNo, -1041);
            return false;
        }

        $orderRow = Db::name('vip_order')->where('order_no', $tradeRow['order_no'])->find();
        if (! $orderRow) {
            self::exceptionError('购买VIP订单不存在', -1051);
            return false;
        }
        if ($orderRow['status'] >= 2) {
            self::exceptionError('购买VIP订单已经完成了', -1052);
            return false;
        }

        $currentTime = time();
        // 启动事务
        Db::startTrans();
        try{
            // 锁定用户表
            $userRow = Db::name('user')->lock(true)->find($orderRow['user_id']);

            //获取最新到期时间
            $newExpireTime = VipModule::getNewExpireTime($userRow['vip_expire_time'], $orderRow['type']);

            // 变更订单状态
            Db::name('pay_trade')->where('trade_no', $tradeNo)->update([
                'status' => 2,
                'finish_time' => $currentTime,
                'extra' => $extraStr
            ]);

            Db::name('vip_order')->where('order_no', $orderRow['order_no'])->update(
                ['status' => 2, 'pay_channel' => $tradeRow['trade_channel'], 'finish_time' => $currentTime]
            );

            // 增加用户vip到期时间
            Db::name('user')->where('id', $orderRow['user_id'])->update(['vip_expire_time' => $newExpireTime]);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            self::exceptionError('系统错误：' . $e->getMessage(), -9999);
            return false;
        }

        return true;
    }

    /**
     * 获取支付参数
     * @return array
     */
    public static function getPayInitConfig()
    {
        $pay_alipay = cmf_get_option('pay_alipay');
        $pay_wxpay = cmf_get_option('pay_wxpay');

        return [
            // 微信支付参数
            'wechat' => [
                'debug'      => false, // 沙箱模式
                'app_id'     => !empty($pay_wxpay['app_id']) ? $pay_wxpay['app_id'] : '', // 应用ID
                'mch_id'     => !empty($pay_wxpay['mch_id']) ? $pay_wxpay['mch_id'] : '', // 微信支付商户号
                'mch_key'    => !empty($pay_wxpay['mch_key']) ? $pay_wxpay['mch_key'] : '', // 微信支付密钥
                'ssl_cer'    => !empty($pay_wxpay['ssl_cer']) ? CMF_ROOT . $pay_wxpay['ssl_cer'] : '', // 微信证书 cert 文件
                'ssl_key'    => !empty($pay_wxpay['ssl_key']) ? CMF_ROOT . $pay_wxpay['ssl_key'] : '', // 微信证书 key 文件
                'notify_url' => url('/app/pay/wxpayCallback', '', 'html', true), // 支付通知URL
                'cache_path' => CMF_ROOT.'data/runtime/',// 缓存目录配置（沙箱模式需要用到）
            ],
            // 支付宝支付参数
            'alipay' => [
                'debug'       => false, // 沙箱模式
                'app_id'      => !empty($pay_alipay['app_id']) ? $pay_alipay['app_id'] : '', // 应用ID
                'public_key'  => !empty($pay_alipay['public_key']) ? $pay_alipay['public_key'] : '', // 支付宝公钥(1行填写)
                'private_key' => !empty($pay_alipay['private_key']) ? $pay_alipay['private_key'] : '', // 支付宝私钥(1行填写)
                'notify_url'  => url('/app/pay/alipayCallback', '', 'html', true), // 支付通知URL
            ]
        ];
    }

    /**
     * 生成商品订单编号
     * @return string
     */
    public static function createOrderNo()
    {
        $timearr = @gettimeofday();
        return @date('YmdHis',$timearr['sec']).intval(substr($timearr['usec'],0,6)).mt_rand(1000,9999).mt_rand(1000,9999);
    }

    /**
     * 生成交易订单编号
     * @return string
     */
    public static function createTradeNo()
    {
        $timearr = @gettimeofday();
        return @date('YmdHis',$timearr['sec']).intval(substr($timearr['usec'],0,6)).mt_rand(1000,9999).mt_rand(1000,9999);
    }

}