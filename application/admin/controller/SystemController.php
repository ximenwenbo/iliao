<?php
/**
 * 系统管理
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class SystemController extends AdminBaseController
{
    /**
     * 系统菜单
     * @author zjy
     * @throws
     */
    public function index()
    {
        echo 1;die;
        return $this->fetch();
    }

}
