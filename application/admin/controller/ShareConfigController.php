<?php
/**
 * 分享配置
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Validate;

class ShareConfigController extends AdminBaseController
{
    /**
     * 分享配置
     * @author zjy
     * @throws
     */
    public function index()
    {
        $option = cmf_get_option('share_config');
        if(!empty($option['share_config'])){
            $website = $option['share_config'];
            $website['share_background_img_file_abs'] = empty($website['share_background_img_file']) ? '' : MaterialService::getFullUrl($website['share_background_img_file']);
            $website['share_logo_file_abs'] = empty($website['share_logo_file']) ? '' : MaterialService::getFullUrl($website['share_logo_file']);
        }else{
            $website = [];
        }
        $this->assign("option", $website);
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
                ['share_install_key','max:100','标题不能超过100个字符'],
                ['share_title','max:50','标题不能超过50个字符'],
                ['share_desc','max:200','标题不能超过200个字符'],
                ['share_url','max:100|url','标题不能超过100个字符|不是正确的url'],
            ]);

            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $data = ['share_config'=>$params];
            if (cmf_set_option('share_config', $data)) {
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
