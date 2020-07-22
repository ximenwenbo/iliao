<?php
/**
 * 用户协议管理
 * @author zjy
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;

class UserAgreementController extends AdminBaseController
{
    /**
     * 机器人列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $settings = cmf_get_option('userprotocol_settings');
        $this->assign("userprotocol_settings", htmlspecialchars_decode($settings['userProtocol']));
        return $this->fetch();
    }


    /**
     * 用户协议配置提交
     * @adminMenu(
     *     'name'   => '用户协议配置提交',
     *     'parent' => 'mob',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '用户协议配置提交',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (empty($param)) {
                return json_encode(['msg'=>'请输入设置的值！', 'code'=>0]);
            }
            $data = ['userProtocol'=>$param['content']];
            if (cmf_set_option('userprotocol_settings', $data)) {
                return json_encode(['msg'=>'保存成功！', 'code'=>0]);

            } else {
                return json_encode(['msg'=>'保存失败，请重新操作！', 'code'=>0]);
            }
        }
    }

}
