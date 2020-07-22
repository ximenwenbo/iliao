<?php
/**
 * 代理商管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\AgentUserService;
use app\admin\service\AgentWithdrawService;
use cmf\controller\AdminBaseController;

class AgentWithdrawController extends AdminBaseController
{
    /**
     * 代理商
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'status' => !isset($params['data']['status']) ? '' : $params['data']['status'],
                'start_time' => empty($params['data']['startDate']) ? '' : $params['data']['startDate'],
                'end_time' => empty($params['data']['endDate']) ? '' : $params['data']['endDate'],
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $admin_id = cmf_get_current_admin_id();
            if($admin_id != 1){
                $condition['agent_id'] = AgentUserService::ToInfo(['id'=>$admin_id],'admin_uid',-1);
            }
            var_dump($params);die;
            $result = AgentWithdrawService::RList($condition);
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $status = AgentWithdrawService::statusList($val['status']);
                    $filed =  [
                        'id' => $val['id'],
                        'agent_id' => AgentWithdrawService::ToInfo(['id'=>$val['agent_id']],'name',-1),
                        'amount' => $val['user_id'],
                        'apply_time' => $val['apply_time'],
                        'create_time' => $val['create_time'],
                        'status' => $val['status'] == 0 ? '<span style="color: red;">'.$status.'</span>' : $status,
                        'opera' => '<a href="javascript:void(0)" style="text-decoration: none" class="more-operate">更多+</a>'
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
        return $this->fetch();
    }

    /**
     * 提现申请
     */
    public function WithdrawApply(){

    }

}
