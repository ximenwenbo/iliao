<?php
/**
 * User: coase
 * Date: 2019-06-05
 * Time: 14:41
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use dctxyun\Cosapi;
use api\app\module\txyun\YuntongxinModule;
use api\app\module\txyun\TxyunCallbackModule;

/**
 * #####腾讯云的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.腾讯云直播回调
 * 2.腾讯云通信回调
 * 3.
 * ``````````````````
 */
class TxyunController extends RestBaseController
{
    /**
     * 腾讯云直播回调--推流事件通知
     */
    public function yunliveCallback()
    {
        try {
            $body = file_get_contents('php://input');

            // 调用参数日志记录
            Log::write(sprintf('%s，腾讯云回调，接收的数据：%s', __METHOD__, var_export($body,true)),'log');

            // 签名验证
            if (YuntongxinModule::verifyApiSign(json_decode($body, true)) == false) {
                Log::write(sprintf('%s，腾讯云回调，签名验证失败：%s', __METHOD__, var_export($body,true)),'error');
                $this->error('签名验证失败');
            }

            // 获取body参数
            $aRequest = json_decode($body, true);

            # 参数校验
            $validate = new Validate([
                'event_type' => 'require|in:0,1,8,100,200', // 事件类型(0:断流 1:推流 8:混流 100:新录制文件 200:新截图文件)
                'stream_id'=> 'require',
                'stream_param' => 'require',
            ]);
            if (! $validate->check($aRequest)) {
                // 调用参数日志记录
                Log::write(sprintf('%s，腾讯云回调，参数错误：%s', __METHOD__, $validate->getError()),'error');
                $this->error($validate->getError());
            }

            // 解析参数stream_param
            parse_str($aRequest['stream_param'], $streamParam);

            if (! empty($streamParam['groupid'])) { // 实时音视频流回调

                if ($aRequest['event_type'] == 0) { // 断流, 删除数据
                    Db::name('chat_trtc')
                        ->where(['stream_id' => $aRequest['stream_id'], 'status' => 1])
                        ->update(['status' => 0, 'delete_time' => time()]);
                    if (!Db::name('chat_trtc')->where(['home_id' => $streamParam['groupid'], 'status' => 1])->count()) {
                        // 如果该房间下面没有正在通话的用户，则删除房间号
                        Db::name('chat_home')->where('home_id', $streamParam['groupid'])->delete();
                    }

                    // 更新主播状态，活跃
                    Db::name('user_token')->where('user_id', base64_decode($streamParam['userid']))->update(['online_status'=>1]);

                    $this->respondJson(0);

                } elseif ($aRequest['event_type'] == 1) { // 推流，新增数据
                    if (!Db::name('chat_trtc')->where(['stream_id' => $aRequest['stream_id'], 'status' => 1])->count()) {
                        $aInput = [
                            'user_id' => base64_decode($streamParam['userid']),
                            'home_id' => $streamParam['groupid'],
                            'stream_id' => $aRequest['stream_id'],
                            'create_time' => time()
                        ];
                        Db::name('chat_trtc')->insert($aInput);
                    }

                    // 更新主播状态，视频中
                    Db::name('user_token')->where('user_id', base64_decode($streamParam['userid']))->update(['online_status'=>4]);

                    $this->respondJson(0);
                } else {
                    $this->respondJson(0);
                }
            }

            if (isset($streamParam['stream_auto_id'])) { // 直播流回调
                $streamFind = Db::name('live_stream')->where('id', $streamParam['stream_auto_id'])->find();
                if (! $streamFind || $streamFind['stream_id'] != $aRequest['stream_id']) {
                    Log::write(sprintf('%s，腾讯云回调，直播流不存在，$aRequest：%s', __METHOD__, var_export($aRequest, true)),'error');
                    $this->error('腾讯云回调直播流不存在');
                }

                // 云直播业务回调处理
                switch ($aRequest['event_type']) {
                    case 0: // 断流
                        TxyunCallbackModule::zhiboStream_0($streamFind);
                        break;
                    case 1: // 推流
                        TxyunCallbackModule::zhiboStream_1($streamFind);
                        break;
                    case 100: // 录制
                        TxyunCallbackModule::zhiboStream_100($streamFind, $aRequest);
                        break;
                    default:
                        break;
                }

                $this->respondJson(0);
            }

            $this->respondJson(0);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 腾讯云通信回调
     */
    public function yuntongxinCallback()
    {
        try {
            $body = file_get_contents('php://input');

            // 调用参数日志记录
            Log::write(sprintf('%s，腾讯云回调，接收的数据：%s', __METHOD__, var_export($body,true)),'log');

            // 签名验证
            if (YuntongxinModule::verifyApiSign(json_decode($body, true)) == false) {
                Log::write(sprintf('%s，腾讯云回调，签名验证失败：%s', __METHOD__, var_export($body,true)),'error');
                $this->error('签名验证失败');
            }

            // 获取body参数
            $aRequest = json_decode($body, true);

            # 公共参数校验
            $validate = new Validate([
                'SdkAppid' => 'require', // App 在云通信 IM 分配的应用标识
                'CallbackCommand' => 'require', // 回调命令
                'ClientIP'=> 'require', // 客户端 IP 地址
                'OptPlatform' => 'require', // 客户端平台
            ]);
            if (! $validate->check($aRequest)) {
                // 调用参数日志记录
                Log::write(sprintf('%s，腾讯云回调，公共参数错误：%s', __METHOD__, $validate->getError()),'error');
                $this->error($validate->getError());
            }

//            switch ($aRequest['CallbackCommand']) {
//                case 'State.StateChange': // 状态变更回调
//                    TxyunCallbackModule::afterStateChange($aRequest);
//                    break;
//                case 'Group.CallbackAfterNewMemberJoin': // 新成员入群之后回调
//                    TxyunCallbackModule::afterNewMemberJoin4Live($aRequest);
//                    break;
//                case 'Group.CallbackAfterMemberExit': // 群成员离开之后回调
//                    TxyunCallbackModule::afterMemberExit4Live($aRequest);
//                    break;
//                default :
//                    break;
//            }

            $this->respondJson(0);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 视频处理回调
     */
    public function videoProcessCallback()
    {
        try {
            $validate = new Validate([
                'status' => 'require', // 状态
                'cover_url' => 'require', // 封面截图
            ]);

            $param = $this->request->param();
            Log::write(sprintf('%s，腾讯云回调，视频处理，接收的数据：%s', __METHOD__, var_export($param,true)),'log');

            if (! $validate->check($param)) {
                // 调用参数日志记录
                Log::write(sprintf('%s，腾讯云回调，视频处理，公共参数错误：%s', __METHOD__, $validate->getError()),'error');
                $this->error($validate->getError());
            }

            $coverUri = substr(strstr($param['cover_url'], '.com/'), 5);
            $aCoverUri = explode('.', $coverUri);

            if (!empty($aCoverUri[0])) {
                $objectFind = Db::name('oss_material')
                    ->where('mime_type', 'video')
                    ->whereLike('object', "{$aCoverUri[0]}%")
                    ->field('id')
                    ->find();
                if ($objectFind) {
                    Db::name('oss_material')->where('id', $objectFind['id'])->update(['video_cover_img' => $coverUri]);
                }
            } else {
                Log::write(sprintf('%s，腾讯云回调，视频处理，没有找到资源：%s', __METHOD__, var_export($aCoverUri, true)),'error');
            }

            $this->success('OK');

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取腾讯云COS签名url
     */
//    public function getCosSignUrl()
//    {
//        $this->getUserId();
//
//        try {
//            // 获取COS临时密钥预签名
//            $oCosapi = new Cosapi();
//            $signUrl = $oCosapi->getSignUrl();
//
//            $this->success('OK', ['sign_url' => $signUrl]);
//
//        } catch (Exception $e) {
//            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
//
//            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
//        }
//    }
}
