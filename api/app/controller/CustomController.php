<?php
/**
 * User: coase
 * Date: 2019-03-11
 */
namespace api\app\controller;

use cmf\controller\RestBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\ConfigModule;

/**
 * #####运营客服的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.添加用户到客服
 * 2.获取客服可用的语音列表
 * 3.添加客服使用语音
 * ``````````````````
 */
class CustomController extends RestBaseController
{
    /**
     * 添加用户到客服
     */
//    public function addAttributeUser()
//    {
//        try {
//            $validate = new Validate([
//                'user_id' => 'require|integer', // 用户id
//            ]);
//
//            $validate->message([
//                'user_id.require' => '请输入用户id!',
//            ]);
//
//            $param = $this->request->param();
//            if (!$validate->check($param)) {
//                $this->error($validate->getError());
//            }
//
//            $userId = $this->getUserId();
//
//            if ($userId == $param['user_id']) {
//                $this->error('参数非法');
//            }
//
//            $userInfo = Db::name("user")->find($param['user_id']);
//            if (! $userInfo) {
//                $this->error('参数非法');
//            }
//
//            $relationFind = Db::name("user_invite_relation")->where('beinvite_user_id', $param['user_id'])->find();
//            if ($relationFind && $relationFind['invite_user_id'] != $userId) {
//                $this->error('该用户已经被别的客服添加过了');
//            }
//
//            if (! $relationFind) {
//                // 新增
//                Db::name("user_invite_relation")->insert([
//                    'invite_user_id' => $userId,
//                    'beinvite_user_id' => $param['user_id'],
//                    'create_time' => time()
//                ]);
//            }
//
//            $this->success("OK");
//
//        } catch (Exception $e) {
//            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
//
//            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
//        }
//    }

    /**
     * 取消用户到客服
     */
//    public function cancelAttributeUser()
//    {
//        try {
//            $validate = new Validate([
//                'user_id' => 'require|integer', // 用户id
//            ]);
//
//            $validate->message([
//                'user_id.require' => '请输入用户id!',
//            ]);
//
//            $param = $this->request->param();
//            if (!$validate->check($param)) {
//                $this->error($validate->getError());
//            }
//
//            $userId = $this->getUserId();
//
//            if ($userId == $param['user_id']) {
//                $this->error('参数非法');
//            }
//
//            $userInfo = Db::name("user")->find($param['user_id']);
//            if (! $userInfo) {
//                $this->error('参数非法');
//            }
//
//            $attributeCount = Db::name("user_invite_relation")
//                ->where('invite_user_id', $userId)
//                ->where('beinvite_user_id', $param['user_id'])
//                ->count();
//            if (! $attributeCount) {
//                $this->error('该用户不属于你');
//            }
//
//            // 删除
//            Db::name("user_invite_relation")->where(['invite_user_id' => $userId, 'beinvite_user_id' => $param['user_id']])->delete();
//
//            $this->success("OK");
//
//        } catch (Exception $e) {
//            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
//
//            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
//        }
//    }

    /**
     * 获取客服可用的语音列表
     */
    public function getUsableSpeechList()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 用户id
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            $userInfo = Db::name("user")->find($param['user_id']);
            if (! $userInfo) {
                $this->error('参数非法');
            }

            // 获取父分类列表
            $parentSelect = Db::name("custom_speech")->where('parent_id = 0 and status = 1')->field('id,name')->select()->toArray();
            if (empty($parentSelect)) {
                $this->error('数据为空');
            }
            foreach ($parentSelect as $item) {
                $speechList[$item['id']] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'speech_list' => []
                ];
            }

            // 获取已经被其它客服使用过的语音id
            $unusableSpeechIds = Db::name('custom_speech_record')
                ->where('custom_user_id <> ' . $userId)
                ->where('user_id', $param['user_id'])
                ->column('speech_id');

            $speechSelect = Db::name("custom_speech")
                ->whereNotIn('id', $unusableSpeechIds)
                ->where('parent_id > 0 AND status = 1')
                ->select();
            if (empty($speechSelect)) {
                $this->error('数据为空');
            }
            foreach ($speechSelect as $value) {
                $speechList[$value['parent_id']]['speech_list'][] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'description' => $value['description'],
                    'speech_url' => $value['speech_url'],
                ];
            }

            $this->success("OK", [
                'list' => $speechList ? array_values($speechList) : [],
                'extra' => [
                    'object_domain' => ConfigModule::getObjectDomain(),
                ]
            ]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 添加客服使用语音
     */
    public function addUsedSpeech()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer', // 用户id
                'speech_id' => 'require|integer', // 语音id
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!',
                'speech_id.require' => '请输入语音id!',
            ]);

            $param = $this->request->param();
            if (! $validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId = $this->getUserId();

            $userInfo = Db::name("user")->find($param['user_id']);
            if (! $userInfo) {
                $this->error('参数非法');
            }

            $speechRecordCount = Db::name('custom_speech_record')
                ->where('custom_user_id', $userId)
                ->where('user_id', $param['user_id'])
                ->where('speech_id', $param['speech_id'])
                ->count();

            if (! $speechRecordCount) {
                $addSpeechRecord = [
                    'custom_user_id' => $userId,
                    'user_id' => $param['user_id'],
                    'speech_id' => $param['speech_id'],
                    'create_time' => time()
                ];
                Db::name('custom_speech_record')->insert($addSpeechRecord);
            }

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
