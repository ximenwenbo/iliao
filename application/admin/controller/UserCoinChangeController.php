<?php
/**
 * 用户金币变更记录
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\UserBalanceLogService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;

class UserCoinChangeController extends AdminBaseController
{
    /**
     * 记录列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        //table表单请求数据
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
                'start_time' => isset($params['data']['startDate']) ? $params['data']['startDate'] : '',
                'end_time' => isset($params['data']['endDate'])  ? $params['data']['endDate'] : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];
            $result = UserBalanceLogService::RList($condition);
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $filed =  [
                        'id' => $value['id'],
                        'user_id' => $value['user_nickname'].' <small>( '.$value['user_id'].' )</small>',
                        'change_alter' => $value['balance'] - $value['change'],
                        'change' => $value['change'],
                        'balance' => $value['balance'] > 0 ? $value['balance'] : 0,
                        'changer_uid' => UserMemberService::ToInfo(['id'=>$value['changer_uid']],'user_nickname',-1).' <small>( '.$value['changer_uid'].' )</small>',
                        'changer_ip' => $value['changer_ip'],
                        'description' => $value['description'],
                        'create_time' => date("Y-m-d H:i:s",$value['create_time']),
                    ];
                    array_push($data,$filed);
                }
            }
            return json_encode([
                "pageIndex"=> $params['pageIndex'],//分页索引
                "pageSize"=> $params['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "sortField"=> $condition['sortField'],//排序字段
                "sortType"=> $condition['sortType'],//排序类型
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $params['data']//表单参数
            ]);
        }
        return $this->fetch();
    }

}
