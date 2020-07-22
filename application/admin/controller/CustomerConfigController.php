<?php
/**
 * 客服配置
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Validate;

class CustomerConfigController extends AdminBaseController
{
    /**
     * 配置页面
     * @author zjy
     * @throws
     */
    public function index()
    {
        //用户等级
        $option = cmf_get_option('customer_config');
        $this->assign("option", $option['customer_config']);
        return $this->fetch('given_config/customer_config/index');
    }

    /**
     * 配置提交
     * @throws
     */
    public function indexPost(){
        if($this->request->isPost()){
            $param = $this->request->post('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                ['customer.uid','require','不能为空值'],
            ]);
            if(! $validate->check($params)){
                $this->error($validate->getError());
            }
            $data = ['customer_config'=>$params];
            if (cmf_set_option('customer_config', $data)) {
                cmf_clear_cache();
                $this->success('保存配置完成');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }

        $this->error('无权访问');

    }
}
