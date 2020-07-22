<?php
/**
 * 客服管理
 * @author zjy
 * @date 2019/06/05
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class CompanyStaffController extends AdminBaseController
{
    /**
     * 客服列表
     * @throws
     */
    public function index()
    {
        return $this->fetch();
    }

}
