<?php
/**
 * User: coase
 * Date: 2018-11-01
 * Time: 14:14
 */
namespace api\apph5\controller;

use cmf\controller\HomeBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\MaterialModule;

/**
 * #####支付H5模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.支付页面
 * 2.
 * ``````````````````
 */
class ShareController extends HomeBaseController
{
    /**
     * 支付方式页面
     */
    public function index()
    {
        $this->assign('money', 111);
        return $this->fetch('');
    }

    /**
     * shareinstall-H5推广分享页
     * @return mixed
     */
    public function shareinstall()
    {
        $option = cmf_get_option('share_config');
        $website = $option['share_config'];
        $data = [
            'share_install_key' => array_key_exists('share_install_key', $website) ? $website['share_install_key'] : '',
            'title' => array_key_exists('share_title', $website) ? $website['share_title'] : '分享标题',
            'desc' => array_key_exists('share_desc', $website) ? $website['share_desc'] : '分享描述',
            'logo' => array_key_exists('share_logo_file', $website) ? MaterialModule::getFullUrl($website['share_logo_file']) : '',
            'background_img' => array_key_exists('share_background_img_file', $website) ? MaterialModule::getFullUrl($website['share_background_img_file']) : '',
        ];

        $this->assign('data', $data);

        $domain = $this->request->domain();
        $from_uid = $this->request->param('from_uid');
        $this->assign('domain', $domain);
        $this->assign('from_uid', $from_uid);
        return $this->fetch('');
    }
}
