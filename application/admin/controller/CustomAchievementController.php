<?php
/**
 * 客服业绩管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\PromInviteRelaService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Exception;
use think\Log;
use think\Db;

class CustomAchievementController extends AdminBaseController
{
    /**
     * 客服业绩列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $class_id = [1=>'充值金币', 2=>'充值vip'];
        $this->assign('class_id',$class_id);
        return $this->fetch();
    }

    /**
     * 列表ajax
     * @throws
     */
    public function ListAjax()
    {
        $params = $this->request->param();
        $condition = [
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
            'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = PromInviteRelaService::RList($condition);

        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $parentInfo = null;
                $grandInfo = null;
                if ($val['parent_uid']) {
                    $parentInfo = UserMemberService::ToInfo(['id'=>$val['parent_uid']],'id,user_nickname,from_uid');
                    if ($parentInfo['from_uid']) {
                        $grandInfo = UserMemberService::ToInfo(['id'=>$parentInfo['from_uid']],'id,user_nickname');
                    }
                }
                $filed =  [
                    'id' => $val['id'],
                    'user' => $val['user_nickname'].'<small> ( '.$val['user_id'].' )</small>',
                    'parent' => !empty($parentInfo) ? $parentInfo['user_nickname'].'<small> ( '.$parentInfo['id'].' )</small>' : '',
                    'grand' => !empty($grandInfo) ? $grandInfo['user_nickname'].'<small> ( '.$grandInfo['id'].' )</small>' : '',
                    'level' => $val['level'],
                    "create_time"=> $val['create_time'],
                ];
                array_push($data,$filed);
            }
        }
        return json_encode([
            "pageIndex"=> $params['pageIndex'],//分页索引
            "pageSize"=> $params['pageSize'],//每页显示数量
            "totalPage"=> count($data),//分页记录
            "sortField"=> 'id',//排序字段
            "sortType"=> 'desc',//排序类型
            "total"=> $result['total'],//总记录数
            'pageList'=>$data,//分页数据
            "data"=> $params['data']//表单参数
        ]);
    }

    /**
     * 添加关系
     */
    public function Add()
    {
        if ($this->request->isAjax()) {
            $param = $this->request->param();
            //数据验证
            if (empty($param['be_uid'])) {
                return json_encode(['code'=>0, 'msg'=>'被推广者用户id不能为空']);
            } elseif (! Db::name('user')->where('id', $param['be_uid'])->count()) {
                return json_encode(['code'=>0, 'msg'=>'被推广者用户id不存在']);
            } else {
                if (Db::name('prom_invite_rela')->where('user_id', $param['be_uid'])->count()) {
                    return json_encode(['code'=>0, 'msg'=>'被推广者已经存在推广记录']);
                }
            }
            if (empty($param['from_uid'])) {
                return json_encode(['code'=>0, 'msg'=>'推广者用户id不能为空']);
            } elseif (! Db::name('user')->where('id', $param['from_uid'])->count()) {
                return json_encode(['code'=>0, 'msg'=>'推广者用户id不存在']);
            }
            if ($param['be_uid'] == $param['from_uid']) {
                return json_encode(['code'=>0, 'msg'=>'推广者和被推广者不能是同一个人']);
            }

            // 启动事务
            Db::startTrans();
            try {
                Db::name('user')->where('id', $param['be_uid'])->update(['from_uid' => $param['from_uid']]);

                Db::name('prom_invite_rela')->insert([
                    'user_id' => $param['be_uid'],
                    'parent_uid' => $param['from_uid'],
                    'level' => \api\app\module\promotion\InviteModule::getInvitedLevel($param['from_uid'])
                ]);

                Db::name('user_coin_record')
                    ->whereIn('change_class_id', [1,41,42,43,44])
                    ->where('user_id', $param['be_uid'])
                    ->where('prom_status', 1)
                    ->update([
                        'prom_status' => 0
                    ]);

                // 提交事务
                Db::commit();

                // 推广注册奖励
                \api\app\module\promotion\InviteModule::inviteUserBonusByNewUid($param['be_uid']);

                return json_encode(['code'=>200, 'msg'=>'添加成功', 'data'=>[]]);

            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

                return json_encode(['code'=>0, 'msg'=>'添加失败', 'data'=>[]]);
            }
        }

        return $this->fetch('add');
    }
}
