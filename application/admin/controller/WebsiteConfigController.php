<?php
/**
 * 网站配置
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Validate;

class WebsiteConfigController extends AdminBaseController
{
    /**
     * 网站配置
     * @author zjy
     * @throws
     */
    public function index()
    {
        $option = cmf_get_option('website');
        if(!empty($option['website'])){
            $website = $option['website'];
            $website['android_file_abs'] = empty($website['android_file']) ? '' : MaterialService::getFullUrl($website['android_file']);
            $website['iPhone_file_abs'] = empty($website['iPhone_file']) ? '' : MaterialService::getFullUrl($website['iPhone_file']);
        }else{
            $website = [];
        }
        $this->assign("website", $website);
        return $this->fetch();
    }

    /**
     * 网站配置提交
     * @throws
     */
    public function indexPost(){
        if($this->request->isPost()){
            $param = $this->request->post('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                'maintain_switch' => 'integer|require',
                'maintain_tips' => 'max:200',
                'web_title' => 'max:20',
                'domain' => 'url',
                'copyright_info' => 'max:200',
                'currency_name' => 'max:10',
                'office_tel' => ['regex'=> '/^[0-9]{3,4}-{0,1}[0-9]{7,8}$/i'],
                'office_address' => 'max:120',
            ]);
            $validate->message([
                'maintain_switch.require' => '网站维护开关必须选择',
                'maintain_switch.integer' => '网站维护开关选择有误',
                'maintain_tips.max' => '维护提示不能超过200字',
                'web_title.max' => '网站标题不能超过20字',
                'domain.regex' => '网站域名不符合规则',
                'copyright_info.max' => '版权信息不能超过200字',
                'currency_name.max' => '货币名称不能超过10字',
                'office_tel.regex' => '公司电话不符合规则',
                'office_address.max' => '公司地址不能超过120字',
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $data = ['website'=>$params];
            if (cmf_set_option('website', $data)) {
                cmf_clear_cache();
                $this->success('保存配置完成');
            } else {
                $this->error('保存失败，请重新操作！');
            }

        }else{
            return $this->error('无权访问');
        }
    }

}
