<?php
/**举报页面
 * User: coase
 * Date: 2019-03-19
 */
namespace api\apph5\controller;

use cmf\controller\HomeBaseController;
use \think\Db;
use \think\Log;
use think\Validate;
use think\Exception;

/**
 * #####H5举报页面模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 */
class InformController extends HomeBaseController
{
    /**
     * 举报页
     */
    public function index()
    {
        try{
            $res = Db::name('inform_reason')->order('sort desc')->field('id,reason')->select();
            $domain = $this->request->domain();
            $user_id = $this->request->param('user_id');
            $be_user_id = $this->request->param('be_user_id');
            $this->assign('domain', $domain);
            $this->assign('list',$res);
            $this->assign('user_id',$user_id);
            $this->assign('be_user_id',$be_user_id);
            return $this->fetch();
        }catch(Exception $e){
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            return json_encode(['code'=>0, 'msg'=>'网络异常'.$e->getMessage()]);
        }
    }

    /**
     * 举报内容提交
     */
    public function submitInform()
    {
        try{
            $param = $this->request->param();
            $validate = new Validate([
                'be_user_id' => 'require|integer',
                'reason_id' => 'require|integer',
                'user_id' => 'require|integer',
            ]);

            $validate->message([
                'be_user_id.require' => '被举报者不明确!',
                'reason_id.require' => '请选择举报理由!',
                'user_id.require' => '举报者不明确!',
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            if ($param['be_user_id'] == $param['user_id']) {
                $this->error('不能举报自己');
            }
            Db::name('inform_content')->insert([
                'user_id' => $param['user_id'],
                'be_user_id' => $param['be_user_id'],
                'reason_id' => $param['reason_id'],
                'note' => !empty($param['note']) ? $param['note'] : '',
            ]);
            $this->success("举报成功");
        }catch(Exception $e){
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');
            $this->error('网络异常');
        }
    }
}
