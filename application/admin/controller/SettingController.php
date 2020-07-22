<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\ThemeModel;
use app\admin\service\MaterialService;
use think\Exception;

/**
 * 参数配置控制器
 * Class SettingController
 * @package app\admin\controller
 */
class SettingController extends AdminBaseController
{
    /**
     * 阿里云Oss配置
     * @adminMenu(
     *     'name'   => '阿里云Oss配置',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 0,
     *     'icon'   => '',
     *     'remark' => '阿里云Oss配置',
     *     'param'  => ''
     * )
     */
    public function aliyunOss()
    {
        $aliyunOss = cmf_get_option('aliyun_oss');
        $this->assign("aliyun_oss", $aliyunOss);

        return $this->fetch();
    }

    /**
     * 提交阿里云Oss配置
     */
    public function aliyunOssPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('aliyun_oss', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 充值金币转换配置
     */
    public function switchCoin()
    {
        $moneytocoinrate = cmf_get_option('moneytocoinrate');
        $this->assign("moneytocoinrate", $moneytocoinrate);

        return $this->fetch();
    }

    /**
     * 提交充值金币转换配置
     */
    public function switchCoinPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('moneytocoinrate', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 提现参数配置
     */
    public function CashWithdrawal()
    {
        $cashwithdrawalgrade = cmf_get_option('cashwithdrawalgrade');
        $this->assign("cashwithdrawalgrade", $cashwithdrawalgrade);
        return $this->fetch();
    }

    /**
     * 提交提现参数配置
     */
    public function CashWithdrawalPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('cashwithdrawalgrade', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 充值金币配置
     */
    public function rechargeCoin()
    {
        $rechargecoingrade = cmf_get_option('rechargecoingrade');
        $this->assign("rechargecoingrade", $rechargecoingrade['list']);
        $this->assign('active',1);
        return $this->fetch();
    }

    /**
     * 提交充值金币配置
     */
    public function rechargeCoinPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('rechargecoingrade', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 充值VIP配置
     */
    public function rechargeVip()
    {
        $rechargevipgrade = cmf_get_option('rechargevipgrade');
        $this->assign("rechargevipgrade", $rechargevipgrade['list']);

        return $this->fetch();
    }

    /**
     * 提交充值VIP配置
     */
    public function rechargeVipPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('rechargevipgrade', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 联系方式配置
     * @adminMenu(
     *     'name'   => '联系方式配置',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 0,
     *     'icon'   => '',
     *     'remark' => '联系方式配置',
     *     'param'  => ''
     * )
     */
    public function contact()
    {
        $settings = cmf_get_option('contact_settings');
        $this->assign("contact_settings", $settings);

        return $this->fetch();
    }

    /**
     * 联系方式配置提交
     * @adminMenu(
     *     'name'   => '联系方式配置提交',
     *     'parent' => 'mob',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '联系方式配置提交',
     *     'param'  => ''
     * )
     */
    public function contactPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('contact_settings', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 系统消息发送者配置
     * @adminMenu(
     *     'name'   => '系统消息发送者配置',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 0,
     *     'icon'   => '',
     *     'remark' => '系统消息发送者配置',
     *     'param'  => ''
     * )
     */
    public function sysNotice()
    {
        $settings = cmf_get_option('sys_notice_settings');
        if (! empty($settings['avatar'])) {
            $settings['avatar'] = MaterialService::getFullUrl($settings['avatar']);
        }

        $this->assign("sys_notice_settings", $settings);

        return $this->fetch();
    }

    /**
     * 短信配置
     */
    public function sms()
    {
        $sms_conf = cmf_get_option('sms_conf');
        $sms_aliyun_dayu = cmf_get_option('sms_aliyun_dayu');
        $sms_qcloud = cmf_get_option('sms_qcloud');
        $this->assign("sms_conf", $sms_conf);
        $this->assign("sms_aliyun_dayu", $sms_aliyun_dayu);
        $this->assign("sms_qcloud", $sms_qcloud);

        return $this->fetch();
    }

    /**
     * 提交短信配置
     */
    public function smsPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if (empty($post['service'])) {
                $this->error('请选择短信服务商！');
            }

            if (cmf_set_option('sms_conf', ['type' => $post['service']])) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }

            $this->error('配置失败，请重新操作！');
        }
    }

    /**
     * 短信服务商配置
     * @return mixed
     */
    public function smsType()
    {
        $type = $this->request->param('type');
        if (empty($type)) {
            $this->error('请求有误！');
        }

        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            switch ($type) {
                case 'aliyun_dayu': // 阿里大鱼短信
                    if (empty($post['sms_aliyun_dayu'])) {
                        $this->error('请配置参数！');
                    }
                    if (cmf_set_option('sms_aliyun_dayu', $post['sms_aliyun_dayu'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                case 'qcloud_sms': // 腾讯云短信
                    if (empty($post['sms_qcloud'])) {
                        $this->error('请配置参数！');
                    }
                    if (cmf_set_option('sms_qcloud', $post['sms_qcloud'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                default:
                    $this->error('参数有误，请重新操作！');
            }
        }
    }

    /**
     * 支付配置
     */
    public function pay()
    {
        $pay_conf = cmf_get_option('pay_conf');
        $this->assign("channel", isset($pay_conf['channel']) ? $pay_conf['channel'] : []);

        $pay_alipay = cmf_get_option('pay_alipay');
        $this->assign("pay_alipay", $pay_alipay);

        $pay_wxpay = cmf_get_option('pay_wxpay');
        $this->assign("pay_wxpay", $pay_wxpay);

        return $this->fetch();
    }

    /**
     * 提交支付配置
     */
    public function payPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if (empty($post['channel'])) {
                $this->error('请至少开通一个支付渠道！');
            }

            $channel = [];
            if (!empty($post['channel']['alipay'])) {
                $channel['alipay'] = 'alipay';
            }
            if (!empty($post['channel']['wxpay'])) {
                $channel['wxpay'] = 'wxpay';
            }

            if (empty($channel)) {
                $this->error('请至少开通一个支付渠道！');
            }

            if (cmf_set_option('pay_conf', ['channel' => $channel])) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 支付渠道配置
     * @author coase
     */
    public function payChannel()
    {
        $channel = $this->request->param('channel');
        if (empty($channel)) {
            $this->error('请求有误！');
        }

        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            switch ($channel) {
                case 'alipay':
                    if (empty($post['pay_alipay'])) {
                        $this->error('请配置支付宝参数！');
                    }
                    if (cmf_set_option('pay_alipay', $post['pay_alipay'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                case 'wxpay':
                    if (empty($post['pay_wxpay'])) {
                        $this->error('请配置微信支付参数！');
                    }
                    $sslcerFile = $this->request->file('ssl_cer');
                    $sslkeyFile = $this->request->file('ssl_key');
                    if (empty($sslcerFile) || empty($sslkeyFile)) {
                        $this->error('请上传微信支付密钥文件！');
                    }

                    // 保存ssl_cer.pem文件
                    $sslcerInfo = $sslcerFile->validate([
                        'ext' => 'pem'
                    ]);
                    if (! $sslcerInfo->check()) {
                        $this->error($sslcerFile->getError()); // 上传失败获取错误信息
                    }
                    $sslcerInfo = $sslcerInfo->move(ROOT_PATH . 'public' . DS . 'upload'.DS.'pay', 'ssl_cer.pem');
                    $sslcerPathName = 'public/upload/pay/' . $sslcerInfo->getSaveName();

                    // 保存ssl_key.pem文件
                    $sslkeyInfo = $sslkeyFile->validate([
                        'ext' => 'pem'
                    ]);
                    if (! $sslkeyInfo->check()) {
                        $this->error($sslkeyInfo->getError()); // 上传失败获取错误信息
                    }
                    $sslkeyInfo = $sslkeyInfo->move(ROOT_PATH . 'public' . DS . 'upload'.DS.'pay', 'ssl_key.pem');
                    $sslkeyPathName = 'public/upload/pay/' . $sslkeyInfo->getSaveName();

                    $post['pay_wxpay'] ['ssl_cer'] = $sslcerPathName;
                    $post['pay_wxpay'] ['ssl_key'] = $sslkeyPathName;
                    if (cmf_set_option('pay_wxpay', $post['pay_wxpay'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                default:
                    $this->error('参数有误，请重新操作！');
            }
        }
    }

    /**
     * 登录配置
     * @author coase
     */
    public function login()
    {
        $login_conf = cmf_get_option('login_conf');
        $this->assign("type", isset($login_conf['type']) ? $login_conf['type'] : []);

        $login_weixin = cmf_get_option('login_weixin');
        $this->assign("login_weixin", $login_weixin);

        $login_qq = cmf_get_option('login_qq');
        $this->assign("login_qq", $login_qq);

        return $this->fetch();
    }

    /**
     * 提交登录配置
     */
    public function loginPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if (empty($post['type'])) {
                $this->error('请至少开通一个登录方式！');
            }

            $type = [];
            if (!empty($post['type']['weixin'])) {
                $type['weixin'] = 'weixin';
            }
            if (!empty($post['type']['qq'])) {
                $type['qq'] = 'qq';
            }

            if (empty($type)) {
                $this->error('请至少开通一个支付渠道！');
            }

            if (cmf_set_option('login_conf', ['type' => $type])) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 登录方式配置
     * @return mixed
     */
    public function loginType()
    {
        $type = $this->request->param('type');
        if (empty($type)) {
            $this->error('请求有误！');
        }

        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            switch ($type) {
                case 'weixin':
                    if (empty($post['login_weixin'])) {
                        $this->error('请配置微信登录参数！');
                    }
                    if (cmf_set_option('login_weixin', $post['login_weixin'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                case 'qq':
                    if (empty($post['login_qq'])) {
                        $this->error('请配置QQ登录参数！');
                    }
                    if (cmf_set_option('login_qq', $post['login_qq'])) {
                        cmf_clear_cache();
                        $this->success('配置成功！');
                    } else {
                        $this->error('配置失败，请重新操作！');
                    }
                    exit;
                default:
                    $this->error('参数有误，请重新操作！');
            }
        }
    }

    /**
     * 腾讯云配置
     */
    public function trtc()
    {
        $trtc = cmf_get_option('trtc');
        $this->assign("trtc", $trtc);

        return $this->fetch();
    }

    /**
     * 提交腾讯云配置
     */
    public function trtcPost()
    {
        $trtc = cmf_get_option('trtc');

        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            $privateFile = $this->request->file('private_pem');
            $publicFile = $this->request->file('public_pem');

            // 保存私钥文件
            if (! empty($privateFile)) {
                $privateInfo = $privateFile->validate([
                    'size' => '1024'
                ]);
                if (!$privateInfo->check()) {
                    $this->error($privateInfo->getError()); // 上传失败获取错误信息
                }
                $privateInfo = $privateInfo->move(ROOT_PATH . 'publication' . DS . 'upload' . DS . 'trtc', 'private_key');
                $privatePathName = 'publication/upload/trtc/' . $privateInfo->getSaveName();
                $post['private_pem'] = $privatePathName;
            } else {
                $post['private_pem'] = !empty($trtc['private_pem']) ? $trtc['private_pem'] : '';
            }

            // 保存公钥文件
            if (! empty($publicFile)) {
                $publicInfo = $publicFile->validate([
                    'size' => '1024'
                ]);
                if (!$publicInfo->check()) {
                    $this->error($publicInfo->getError()); // 上传失败获取错误信息
                }
                $publicInfo = $publicInfo->move(ROOT_PATH . 'publication' . DS . 'upload' . DS . 'trtc', 'public_key');
                $publicPathName = 'publication/upload/trtc/' . $publicInfo->getSaveName();
                $post['public_pem'] = $publicPathName;
            } else {
                $post['public_pem'] = !empty($trtc['public_pem']) ? $trtc['public_pem'] : '';
            }

            if (cmf_set_option('trtc', $post)) {
                cmf_clear_cache();
                $this->success('配置成功！');
            } else {
                $this->error('配置失败，请重新操作！');
            }
        }
    }

    /**
     * 地图定位配置
     */
    public function position()
    {
        $position = cmf_get_option('position');
        $this->assign("position", $position);

        return $this->fetch();
    }

    /**
     * 提交地图定位配置
     */
    public function positionPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('position', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 用户协议配置
     */
    public function userProtocol()
    {
        $settings = cmf_get_option('userprotocol_settings');
        $this->assign("userprotocol_settings", htmlspecialchars_decode($settings['userProtocol']));
        return $this->fetch();
    }

    /**
     * 提交用户协议配置
     */
    public function userProtocolPost()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param)) {
                return json_encode(['msg'=>'请输入设置的值！', 'code'=>0]);
            }
            $data = ['userProtocol'=>$param['content']];
            if (cmf_set_option('userprotocol_settings', $data)) {
                cmf_clear_cache();
                return json_encode(['msg'=>'保存成功！', 'code'=>0]);
            } else {
                return json_encode(['msg'=>'保存失败，请重新操作！', 'code'=>0]);
            }
        }
    }

    /**
     * 隐私协议
     * @return mixed|string
     */
    public function privacyProtocol()
    {
        $settings = cmf_get_option('privacyprotocol_settings');
        $this->assign("privacyprotocol_settings", htmlspecialchars_decode($settings['privacy_protocol']));
        return $this->fetch();
    }

    /**
     * 提交隐私协议
     */
    public function privacyProtocolPost()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param)) {
                return json_encode(['msg'=>'请输入设置的值！', 'code'=>0]);
            }
            $data = ['privacy_protocol' => $param['content']];
            if (cmf_set_option('privacyprotocol_settings', $data)) {
                cmf_clear_cache();
                return json_encode(['msg'=>'保存成功！', 'code'=>0]);
            } else {
                return json_encode(['msg'=>'保存失败，请重新操作！', 'code'=>0]);
            }
        }
    }

    /**
     * 手机号白名单配置,登录使用
     */
    public function mobileWhite()
    {
        $mobilewhilte = cmf_get_option('mobilewhilte');
        $this->assign("mobilewhilte", $mobilewhilte);

        return $this->fetch();
    }

    /**
     * 提交手机号白名单配置,登录使用
     */
    public function mobileWhitePost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('mobilewhilte', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 推广分成配置
     */
    public function promRatio()
    {
        $setting = cmf_get_option('divideproportion');
        $this->assign("prominvite", $setting);

        return $this->fetch();
    }

    /**
     * 提交推广分成配置
     */
    public function promRatioPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('divideproportion', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 水印设置
     */
    public function watermark()
    {
        $setting = cmf_get_option('watermark');
        $this->assign("watermark", $setting);

        return $this->fetch();
    }

    /**
     * 水印设置提交
     */
    public function watermarkPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if(empty($post['image_status'])) {
                $post['image_status'] = 0;
            }
            if(empty($post['video_status'])) {
                $post['video_status'] = 0;
            }

            if (cmf_set_option('watermark', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * vip限制设置
     */
    public function vipLimit(){
        $setting = cmf_get_option('vipLimit');
        $setting['checked_1'] = !empty($setting['status']) && $setting['status'] == 1 ? 'checked' : '';
        $setting['checked_2'] = !empty($setting['status']) && $setting['status'] == 2 ? 'checked' : '';
        $setting['disabled'] = !empty($setting['status']) && $setting['status'] == 2 ? 'disabled' : '';
        $startDate = !empty($setting['startDate']) ? $setting['startDate'] : '';
        $endDate = !empty($setting['endDate']) ? $setting['endDate'] : '';
        if(empty($startDate) || empty($endDate)){
            $setting['old_date'] = '';
        }else{
            $setting['old_date'] = $startDate.' 至 '. $endDate;
        }

        $this->assign("vipLimit", $setting);
        return $this->fetch();
    }

    /**
     * vip限制配置提交
     */
    public function vipLimitPost(){
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if( !empty($post['status']) && $post['status'] == 1) {
                if($post['startDate'] == '' || $post['endDate'] == ''){
                    $this->error('限制时间段不能为空！');
                }
            }

            if (cmf_set_option('vipLimit', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }


    /**
     * vip限制设置
     */
    public function levelLimit(){
        $user_level_setting = cmf_get_option('user_level_setting');
        $setting = cmf_get_option('levelLimit');

        !isset($setting['chat']) && $setting['chat'] = 0;
        !isset($setting['voice']) && $setting['voice'] = 0;
        !isset($setting['video']) && $setting['video'] = 0;
        !isset($setting['livebulletscreen']) && $setting['livebulletscreen'] = 0;

        $this->assign("userLevel", $user_level_setting['list']);
        $this->assign("levelLimit", $setting);
        return $this->fetch();
    }


    /**
     *vip限制设置Post
     */
    public function levelLimitPost(){
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if (cmf_set_option('levelLimit', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 固定文字
     */
    public function fixedText(){
        $setting = cmf_get_option('fixedText');
        $this->assign("fixedText", $setting);
        return $this->fetch();
    }

    /**
     * 固定文字POST
     */
    public function fixedTextPost(){
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            if (cmf_set_option('fixedText', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 固定文字
     */
    public function giftAnnounce(){
        $setting = cmf_get_option('giftAnnounce');
        $this->assign("giftAnnounce", $setting);
        return $this->fetch();
    }

    /**
     * 固定文字POST
     */
    public function giftAnnouncePost(){
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }
            $post['coin'] = !empty($post['coin']) && intval($post['coin']) ? intval($post['coin']) : 0;
            if (cmf_set_option('giftAnnounce', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }


    /**
     * 社区设置
     */
    public function setCommunity(){
        $setting = cmf_get_option('setCommunity');
        $setting['status'] = empty($setting['status']) ?  1 : $setting['status'];
        $this->assign("setCommunity", $setting);
        return $this->fetch();
    }


    /**
     * 固定文字POST
     */
    public function setCommunityPost(){
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post['status'])) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('setCommunity', $post)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

}
