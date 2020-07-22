<?php
/**
 * User: coase
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;;

/**
 * #####举报的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.新增举报
 * 2.获取举报理由列表
 * ``````````````````
 */
class InformController extends RestUserBaseController
{
    /**
     * 新增举报
     */
    public function addInform()
    {
        $userId = $this->getUserId();

        try {
            $validate = new Validate([
                'be_user_id' => 'require|integer',
                'reason_id' => 'require|integer',
            ]);

            $validate->message([
                'be_user_id.require' => '请输入被举报者id!',
                'reason_id.require' => '请输入举报理由!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($param['be_user_id'] == $userId) {
                $this->error('不能举报自己');
            }

            Db::name('inform_content')->insert([
                'user_id' => $userId,
                'be_user_id' => $param['be_user_id'],
                'reason_id' => $param['reason_id'],
                'note' => !empty($param['note']) ? $param['note'] : '',
            ]);

            $this->success("OK");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 获取举报理由列表
     */
    public function getReasonList()
    {
        try {
            $list = Db::name('inform_reason')
                ->field('id reason_id,reason')
                ->order('sort', 'asc')
                ->select();

            $this->success("OK", ['list' => $list]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
