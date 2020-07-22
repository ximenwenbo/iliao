<?php
/**
 * 清除缓存
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class ClearCacheController extends AdminBaseController
{
    /**
     * 清除缓存
     * @adminMenu(
     *     'name'   => '清除缓存',
     *     'parent' => 'default',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '清除缓存',
     *     'param'  => ''
     * )
     */
    public function action()
    {
        if ($this->request->isAjax()) {
            cmf_clear_cache();

            return json_encode(['msg'=>'缓存清除成功', 'code'=>200]);
        }
    }

}
