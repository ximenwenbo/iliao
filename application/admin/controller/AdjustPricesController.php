<?php
/**
 * 申请调价配置
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Validate;

class AdjustPricesController extends AdminBaseController
{
    /**
     * 分享配置
     * @author zjy
     * @throws
     */
    public function index()
    {
        $option = cmf_get_option('adjust_Prices');
        $this->assign("option", htmlspecialchars_decode($option['adjust_Prices']['content']));
        return $this->fetch('given_config/adjust_prices/index');
    }

    /**
     * 网站配置提交
     * @throws
     */
    public function indexPost(){
        if($this->request->isPost()){
            $param = $this->request->post();
            $validate = new Validate([
                ['content','require','内容不能为空'],
            ]);

            if(! $validate->check($param)){
                $this->error($validate->getError());
            }

            $data = ['adjust_Prices'=>$param];
            if (cmf_set_option('adjust_Prices', $data)) {
                cmf_clear_cache();
                $this->success('保存配置完成');
            } else {
                $this->error('保存失败，请重新操作！');
            }

        }

        $this->error('无权访问');
    }

}
