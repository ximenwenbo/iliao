<?php
/**
 * User: zjy
 * Date: 2019-09-27
 * Time: 14:29
 */
namespace api\apph5\controller;

use cmf\controller\HomeBaseController;

/**
 * ``````````````````
 * 1.调价申请页面
 * ``````````````````
 */
class AdjustPricesController extends HomeBaseController
{
    /**
     * 申请调价页面
     * @return mixed
     */
    public function index()
    {
        $option = cmf_get_option('adjust_Prices');
        $adjust_prices = htmlspecialchars_decode($option['adjust_Prices']['content']);
        $domain = $this->request->domain();
        $this->assign('domain', $domain);
        $this->assign('adjust_prices', $adjust_prices);
        return $this->fetch('index');
    }
}
