<?php
/**
 * User: coase
 * Date: 2018-10-23
 * Time: 17:50
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;

/**
 * #####用户设置模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 视频语音聊天设置
 * ``````````````````
 */
class SettingController extends RestUserBaseController
{
    // 视频语音聊天设置
    public function videoSpeech()
    {
        try {
            $validate = new Validate([
                'open_video'   => 'integer|in:0,1',
                'video_cost'   => 'integer',
                'open_speech'  => 'integer|in:0,1',
                'speech_cost'  => 'integer',
            ]);

            $validate->message([
                'open_video.integer'   => '开启视频聊天参数错误!',
                'open_speech.integer'  => '开启语音聊天参数错误!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }
            if (empty($param)) {
                $this->error('参数不能都为空');
            }

            $userId = $this->getUserId();
            $userRow = Db::name('user')->find($userId);

            $settingData = [];
            if (isset($param['open_video'])) {
                $settingData['open_video'] = $param['open_video'];
            }
            if (isset($param['video_cost'])) {
                $settingData['video_cost'] = $param['video_cost'];
                if ($userRow['daren_status'] != 2 && $param['video_cost'] > 0) {
                    $this->error('您还不是主播，不能设置更高价格哦');
                }
            }
            if (isset($param['open_speech'])) {
                $settingData['open_speech'] = $param['open_speech'];
            }
            if (isset($param['speech_cost'])) {
                $settingData['speech_cost'] = $param['speech_cost'];
                if ($userRow['daren_status'] != 2 && $param['speech_cost'] > 0) {
                    $this->error('您还不是主播，不能设置更高价格哦');
                }
            }

            $settingRow = Db::name("user_setting")->where('user_id', $userId)->find();

            if (empty($settingRow)) {
                $settingData['user_id'] = $userId;
                Db::name("user_setting")->insert($settingData);
            } else {
                Db::name("user_setting")->where('user_id', $userId)->update($settingData);
                if (isset($settingData['open_video'])) {
                    if ($settingData['open_video'] == 0) {
                        // 开通勿扰
                        Db::name('user_token')->where('user_id', $userId)->update(['online_status' => 5]);
                    } else {
                        // 解除勿扰
                        Db::name('user_token')->where('user_id', $userId)->update(['online_status' => 1]);
                    }
                }
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 开放位置设置
     */
    public function positionSetting()
    {
        try {
            $validate = new Validate([
                'open_position'   => 'require|in:0,1'
            ]);

            $validate->message([
                'open_position.require'   => '参数不能为空!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            Db::name("user")->where('id', $userId)->update(['open_position' => $param['open_position']]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 设置用户消费等级
     */
    public function consumeGrade()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require',
                'consume_grade' => 'require|in:pingmin,xiaokang,fuhao',
            ]);

            $validate->message([
                'user_id.require' => '参数错误!',
                'consume_grade.require' => '参数错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (! Db::name('user')->find($param['user_id'])) {
                $this->error('用户不存在');
            }

            if (Db::name("user_defined_config")->where('user_id', $param['user_id'])->count()) {
                // 存在，更新
                Db::name("user_defined_config")->where('user_id', $param['user_id'])->update([
                    'consume_grade' => $param['consume_grade']
                ]);
            } else {
                // 不存在，新增
                Db::name("user_defined_config")->insert([
                    'user_id' => $param['user_id'],
                    'consume_grade' => $param['consume_grade']
                ]);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 设置用户消费等级
     */
    public function getConsumeGrade()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require',
            ]);

            $validate->message([
                'user_id.require' => '参数错误!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if (! Db::name('user')->find($param['user_id'])) {
                $this->error('用户不存在');
            }

            $consumeGrade = Db::name("user_defined_config")->where('user_id', $param['user_id'])->value('consume_grade');

            $this->success("OK", [
                'consume_grade' => $consumeGrade ? : 'pingmin'
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
