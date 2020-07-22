<?php
/**
 * 公共配置
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Validate;

class PublicConfigController extends AdminBaseController
{
    /**
     * 公共配置
     * @author zjy
     * @throws
     */
    public function index()
    {
        //用户等级
        $user_level = cmf_get_option('user_level_setting');
        $option = cmf_get_option('public_config');
        $this->assign("option", $option['public_config']);
        //var_dump($option);die;
        $this->assign("user_level", $user_level['list']);
        return $this->fetch();
    }

    /**
     * 公共配置提交
     * @throws
     */
    public function indexPost(){
        if($this->request->isPost()){
            $param = $this->request->post('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['RechargeCoin.poor','max:200','充值金币平民填写过长'],
                ['RechargeCoin.fairly','max:200','充值金币小康填写过长'],
                ['RechargeCoin.volvo','max:200','充值金币富豪填写过长'],
                ['RechargeVip.poor','max:200','充值vip平民填写过长'],
                ['RechargeVip.fairly','max:200','充值vip小康填写过长'],
                ['RechargeVip.volvo','max:200','充值vip富豪填写过长'],
                ['Withdraw.rate','number','百分比抽成必须为数字'],
                ['Withdraw.quota','integer','提现额度必须为整数'],
                ['GiftNotice.coin','integer','礼物通告金币必须为整数'],
                ['Notice.text','max:255','公告文字过长'],
            ]);
 
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $data = ['public_config'=>$params];
            if (cmf_set_option('public_config', $data)) {
                cmf_clear_cache();
                $this->success('保存配置完成');
            } else {
                $this->error('保存失败，请重新操作！');
            }

        }else{
            return $this->error('无权访问');
        }
    }

    /**
     * 用户协议
     */
    public function UserAgreement(){
        $settings = cmf_get_option('user_agreement');
        $this->assign("user_agreement", htmlspecialchars_decode($settings['user_agreement']));
        return $this->fetch();
    }

    /**
     * 用户协议提交
     */
    public function UserAgreementPost(){
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param)) {
                return json_encode(['msg'=>'请输入设置的值！', 'code'=>0]);
            }
            $data = ['user_agreement'=> $param['content']];
            if (cmf_set_option('user_agreement', $data)) {
                cmf_clear_cache();
                return json_encode(['msg'=>'保存成功！', 'code'=>0]);
            } else {
                return json_encode(['msg'=>'保存失败，请重新操作！', 'code'=>0]);
            }
        }
    }

    /*隐私协议*/
    public function PrivacyAgreement(){
        $settings = cmf_get_option('privacy_agreement');
        $this->assign("privacy_agreement", htmlspecialchars_decode($settings['privacy_agreement']));
        return $this->fetch();
    }

    /*隐私协议提交*/
    public function PrivacyAgreementPost(){
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param)) {
                return json_encode(['msg'=>'请输入设置的值！', 'code'=>0]);
            }
            $data = ['privacy_agreement' => $param['content']];
            if (cmf_set_option('privacy_agreement', $data)) {
                cmf_clear_cache();
                return json_encode(['msg'=>'保存成功！', 'code'=>0]);
            } else {
                return json_encode(['msg'=>'保存失败，请重新操作！', 'code'=>0]);
            }
        }
    }

    /**
     * 推广分成
     */
    public function DivideInto(){
        $setting = cmf_get_option('divide_into');
        $this->assign("option", $setting);

        return $this->fetch();
    }

    /**
     * 提交推广分成配置
     */
    public function DivideIntoPost()
    {
        if ($this->request->isPost()) {
            $param = $this->request->post('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['InviteUsers.one','integer','必须填写整数'],
                ['InviteUsers.two','integer','必须填写整数'],
                ['RechargeShare.one','integer','必须填写整数'],
                ['RechargeShare.two','integer','必须填写整数'],
                ['AnchorSplit.one','integer','必须填写整数'],
                ['AnchorSplit.two','integer','必须填写整数'],
            ]);

            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            if (empty($params)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('divide_into', $params)) {
                cmf_clear_cache();
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }
}
