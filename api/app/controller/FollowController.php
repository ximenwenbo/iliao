<?php
/**
 * User: coase
 * Date: 2018-10-23
 * Time: 14:25
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;

/**
 * #####关注模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 添加关注
 * 2. 取消关注
 * ``````````````````
 */
class FollowController extends RestUserBaseController
{
    /**
     * 添加关注
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     *     @param int be_user_id 被关注者uid
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"关注成功!",              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                   //返回code
     *     "msg":"您关注的用户不存在！",                  //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function add()
    {
        try {
            $validate = new Validate([
                'be_user_id' => 'require',
            ]);

            $validate->message([
                'be_user_id.require' => '请输入被关注用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            # 校验被关注者是否存在
            if (Db::name("user")->where('id', $param['be_user_id'])->count() == 0) {
                $this->success("您关注的用户不存在!");
            }

            $userId = $this->getUserId();
            if ($param['be_user_id'] == $userId) {
                $this->success("您不能关注自己!");
            }

            $followRow = Db::name("user_follow")->where(['user_id'=>$userId, 'be_user_id'=>$param['be_user_id']])->find();

            if ($followRow) {
                if ($followRow['status'] == 1) {
                    $this->success("您已经关注过了!");
                } else {
                    Db::startTrans(); // 启动事务
                    try {
                        Db::name('user')->where('id', $param['be_user_id'])->setInc('be_follow_num');
                        Db::name("user_follow")->where(['user_id'=>$userId, 'be_user_id'=>$param['be_user_id']])->update([
                            'status' => 1,
                            'update_time' => time()
                        ]);
                        Db::commit();
                    } catch (Exception $e) {
                        Db::rollback(); // 回滚事务
                        $this->error("操作过快");
                    }
                }
            } else {
                Db::startTrans(); // 启动事务
                try {
                    Db::name('user')->where('id', $param['be_user_id'])->setInc('be_follow_num');

                    Db::name("user_follow")->insert([
                        'user_id' => $userId,
                        'be_user_id' => $param['be_user_id'],
                        'create_time' => time(),
                        'status' => 1
                    ]);
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback(); // 回滚事务
                    $this->error("操作过快");
                }
            }

            $this->success("关注成功!");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error("fail，" . $e->getMessage());
        }
    }

    /**
     * 取消关注
     *
     * @version v1.0.0
     * @author coase
     * @request POST
     * @header
     *     @param string XX-Token 登录token
     *     @param string XX-Device-Type 登录设备
     *     @param string XX-Api-Version 版本（1.0.0）
     * @post
     *     @param int be_user_id 被关注者uid
     * ``````````````````
     * 响应结果如下(成功)：
     * {
     *     'code':1,                      //返回code
     *     'msg':"取关成功!",              //返回message
     *     'data':""
     * }
     * 响应结果如下(失败)：
     * {
     *     "code":0,                                   //返回code
     *     "msg":"您关注的用户不存在！",                  //错误message
     *     "data":""
     * }
     * ``````````````````
     */
    public function cancel()
    {
        try {
            $validate = new Validate([
                'be_user_id' => 'require',
            ]);

            $validate->message([
                'be_user_id.require' => '请输入被关注用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            # 校验被关注者是否存在
            if (Db::name("user")->where('id', $param['be_user_id'])->count() == 0) {
                $this->success("您关注的用户不存在!");
            }

            $userId = $this->getUserId();
            if ($param['be_user_id'] == $userId) {
                $this->success("您不能取消关注自己!");
            }

            $followRow = Db::name("user_follow")->where(['user_id'=>$userId, 'be_user_id'=>$param['be_user_id'], 'status' => 1])->find();
            if (! $followRow) {
                $this->success("您还没有关注过!");
            }
            Db::startTrans(); // 启动事务
            try {
                Db::name('user')->where('id', $param['be_user_id'])->setDec('be_follow_num');
                Db::name("user_follow")->where('id', $followRow['id'])->update(['status' => 0, 'update_time' => time()]);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback(); // 回滚事务
                $this->error("操作过快");
            }
            $this->success("取关成功!");

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error("fail，" . $e->getMessage());
        }
    }
}
