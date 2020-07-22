<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Exception;

/**
 * 消息配置控制器
 * Class MsgConfigController
 * @package app\admin\controller
 */
class MsgConfigController extends AdminBaseController
{
    /**
     * 配置机器人给男用户发送营销类单聊消息
     */
    public function sendMsg2manByRobot()
    {
        $config = cmf_get_option('send_msg_2_man_by_robot');
        $this->assign("send_msg_2_man_by_robot", $config);

        return $this->fetch();
    }

    /**
     * 配置机器人给男用户发送营销类单聊消息
     */
    public function sendMsg2manByRobotPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('send_msg_2_man_by_robot', $post)) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }

    /**
     * 配置女性用户[达人]给男用户发送营销类单聊消息
     */
    public function sendMsg2manByWoman()
    {
        $config = cmf_get_option('send_msg_2_man_by_woman');
        $this->assign("send_msg_2_man_by_woman", $config);

        return $this->fetch();
    }

    /**
     * 提交配置女性用户[达人]给男用户发送营销类单聊消息
     */
    public function sendMsg2manByWomanPost()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post)) {
                $this->error('请输入设置的值！');
            }

            if (cmf_set_option('send_msg_2_man_by_woman', $post)) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败，请重新操作！');
            }
        }
    }
}
