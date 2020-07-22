<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 10:02
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\SmsModule;

/**
 * #####短信功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 发送验证码
 * 2.
 * ``````````````````
 */
class SmsController extends RestBaseController
{
    /**
     * 发送验证码
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header 参数
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post 参数
     *     @param string mobile 手机号
     *     @param int type 类型（1：登录）
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':'1',                    //返回code
     *     'msg':'发送成功!',              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                       //返回code
     *     "msg":"请输入正确的手机号！",      //错误message
     *     "data":""
     * }
     * {
     *     "code":0,                       //返回code
     *     "msg":"验证码发送失败！",          //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function sendCode()
    {
        try {
            $validate = new Validate([
                'mobile' => 'require|number',
                'type' => 'require|in:1', // 1：登录
            ]);

            $validate->message([
                'mobile.require' => '请输入手机号!',
                'type.require' => '请输入验证码类型!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (! cmf_check_mobile($param['mobile'])) {
                $this->error("请输入正确的手机号!");
            }

            # 具体业务分配
            switch ($param['type']) {
                case 1: // 手机号+验证码登录
                    $sendResult = SmsModule::sendSMSCode($param['mobile']);
                    break;
                default:
                    $this->error("type参数有误!");
            }

            if ($sendResult) {
                $this->success("OK");
            } else {
                $this->error(SmsModule::$errMessage);
            }

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
