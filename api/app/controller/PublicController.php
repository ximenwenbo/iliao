<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 10:02
 */
namespace api\app\controller;

use think\Db;
use think\Validate;
use think\Exception;
use cmf\controller\RestBaseController;

/**
 * #####公共发布模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. app配置下发
 * 2. 检测app版本
 * ``````````````````
 */
class PublicController extends RestBaseController
{
    /**
     * app配置下发
     */
    public function configIssue()
    {
        try {
            // 获取登录方式配置
            $login_type = [];
            $login_conf = cmf_get_option('login_conf');
            if (!empty($login_conf['type'])) {
                foreach ($login_conf['type'] as $key=>$type) {
                    $login_type[$key] = cmf_get_option('login_' . $key);
                }
            }

            // 获取腾讯云TRTC配置
            $trtc_option = cmf_get_option('trtc');
            $trtc = [
                'app_id' => $trtc_option['sdkappid'],
                'account_type' => $trtc_option['account_type'],
            ];

            // 获取阿里oss配置
            $oss_option = cmf_get_option('aliyun_oss');
            $oss = [
                'accessKeyId' => $oss_option['accessKeyId'],
                'accessKeySecret' => $oss_option['accessKeySecret'],
                'endpoint' => $oss_option['endpoint'],
                'bucket' => $oss_option['bucket'],
            ];
            // 获取定位配置
            $position = cmf_get_option('position');

            $aConfit = [
                'login_type' => $login_type,
                'trtc' => $trtc,
                'oss' => $oss,
                'storage' => [
                    'cos' => [
                        'region' => $trtc_option['cosArea'],
                        'bucket' => $trtc_option['cosBucket'],
                        'secretId' => $trtc_option['cosSecretId'],
                        'secretKey' => $trtc_option['cosSecretKey'],
                    ]
                ],
                'get_new_heterosexu_user_fresh_time' => 30, // 抢聊页面中刷新时间间隔，单位秒
                'user_message_limit_num' => 10, // todo 用户消息条数限制
                'position' => $position,
                'object_domain' => $trtc_option['cosCdn'],
            ];

            $this->success("OK", ['config' => $aConfit]);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 检测app版本
     */
    public function checkUpdate()
    {
        try {
            $version = $this->request->param('version', ''); // app当前版本号
            $appClass = $this->request->param('app_class', ''); // custom:客服端
            $deviceType = $this->deviceType;

            if ($deviceType == 'android') {
                $system_type = 1; // android
                if ($appClass == 'custom') {
                    $app_class = 2; // android客服端
                } else {
                    $app_class = 1; // android用户端
                }
            } elseif ($deviceType == 'iphone') {
                $system_type = 2; // ios
                $app_class = 1;
            }

            // 获取符合条件的版本号最大的记录
            $newFind = Db::name('app_apk')
                ->where('system_type', $system_type)
                ->where('app_class', $app_class)
                ->where('status', 1)
                ->where('published_time < '.time())
                ->order('app_version', 'desc')
                ->find();

            if ($newFind) {
                $this->success("OK", [
                    'version'       => $newFind['app_version'],
                    'sdk_url'       => $newFind['sdk_url'],
                    'update_msg'    => $newFind['update_msg'],
                    'update_status' => $newFind['update_status'], // 状态 1:不强制更新 2:强制更新
                ]);
            } else {
                $this->success("OK");
            }

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取客服联系方式
     */
    public function getContact()
    {
        try {
            # 获取配置参数
//            $contactConfig = cmf_get_option('contact_settings');

            $aRet = [
                'type' => '客服微信',
                'content' => 'iliao888',
            ];
            $this->success("OK", $aRet);

        } catch (Exception $e) {
            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }


    public function test()
    { //return false;

//var_dump(Db::name('live_home')->where(1)->column('start_time'));die;
        # 测试发送系统消息
//        $r = \app\dcadmin\service\txyun\YuntongxinService::pushSysNotice(57, 'SYS_AUTH_SUCCESS');
        # 测试发送通话中消息
//        $r = \app\dcadmin\service\txyun\YuntongxinService::pushMsg4OnLive(["17","57"], 743, 'notice', '涉黄啦，兄弟');die;
//        var_dump($r);die;


        # 返回数据解码
        $response = 'deyJjb2RlIjoxLdCJtc2ciOiJPSyIsImRhdGEiOnsidXNlcl9pZCI6MTAwMDAwLCJ0b2tlbiI6IjY3MzliY2YzYWVkN2QwODhjMjIzNjIzNTMyZTZjMzcyY2VlMDFlMDE5MmU4YzAxMjc0NDI4MDMxMDQzYTAwNjAiLCJ1c2VyX25pY2tuYW1lIjoiQVx1ODk3Zlx1Njk3YyIsInNleCI6MSwiYWdlIjoxOCwibW9iaWxlIjoiIiwicXEiOiIiLCJ3ZWl4aW4iOiIiLCJhdmF0YXIiOiJodHRwczpcL1wvZGN5dW4tMTI1Nzk5NTU3Ni5jb3MuYXAtc2hhbmdoYWkubXlxY2xvdWQuY29tXC96aGlib1wvMjAxOVwvN1wvMTVcLzk2ZWRhNTg2LWRmZDItNDUzYy05MzcwLTU0NjNlZDU1YzkzNjIwMTkwNzE1MTI2MzYuanBnIiwic2lnbmF0dXJlIjoiXHU4YmU1XHU3NTI4XHU2MjM3XHU1YzFhXHU2NzJhXHU3ZjE2XHU4ZjkxXHU0ZTJhXHU2MDI3XHU3YjdlXHU1NDBkLiIsInNwZWVjaF9pbnRyb2R1Y3Rpb24iOiIiLCJhbGJ1bSI6W10sInZpZGVvIjpbXSwidGFncyI6IiIsImlzX3ZpcCI6MCwidmlwX2V4cGlyZV90aW1lIjoiMTk3MC0wMS0wMSIsImJlX2xvb2tfbnVtIjowLCJiZV9mb2xsb3dfbnVtIjowLCJmb2xsb3dfbnVtIjowLCJwcm92aW5jZV9uYW1lIjoiXHU1Yjg5XHU1ZmJkXHU3NzAxIiwiY2l0eV9uYW1lIjoiXHU1NDA4XHU4MGE1XHU1ZTAyIiwiZGlzdHJpY3RfbmFtZSI6Ilx1ODcwMFx1NWM3MVx1NTMzYSIsIm9wZW5fdmlkZW8iOjEsInZpZGVvX2Nvc3QiOjAsIm9wZW5fc3BlZWNoIjoxLCJzcGVlY2hfY29zdCI6MCwibGFzdF9sb29rX21lIjoiIiwibGFzdF9mb2xsb3dfbWUiOiIiLCJsYXN0X2ZvbGxvdyI6IiIsImF1dGhfc3RhdHVzIjowLCJkYXJlbl9zdGF0dXMiOjAsIm9wZW5fcG9zaXRdpb24iOjF9fQ=';
        $num = hexdec(substr($response, 0, 1));

        $resultA = substr_replace($response, '', 0, 1);var_dump($resultA);
        $resultB = substr_replace($resultA, '', $num, 1);var_dump($resultB);
        $resultC = substr_replace($resultB, '', -$num-1, 1);
        var_dump($resultC);die;
    }
}
