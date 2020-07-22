<?php
/**
 * 客服绩效管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\PromInviteBonusService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;

class CustomServiceController extends AdminBaseController
{
    /**
     * 客服列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $change_class_id = PromInviteBonusService::typeList();
        $this->assign('change_class_id',$change_class_id);
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
            'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
            'change_class_id' => isset($params['data']['change_class_id']) ? $params['data']['change_class_id'] : 0,
            'uuid' => isset($params['data']['uuid']) ? $params['data']['uuid'] : 0,
            'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
            'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
            'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : 10,
            'sortField' => isset($params['sortField']) ? $params['sortField'] : 0,
            'sortType' => isset($params['sortType']) ? $params['sortType'] : 'desc',
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];

        $result = PromInviteBonusService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                switch ($val['invite_level']) {
                    case 1:
                        $levelName = '上级';
                        break;
                    case 2:
                        $levelName = '上上级';
                        break;
                    default:
                        $levelName = '';
                }
                $filed =  [
                    'id' => $val['id'],
                    'from_uid' => $val['user_nickname'].'&nbsp;&nbsp;(&nbsp;<small>'.$val['from_uid'].'</small>&nbsp;)',
                    'user_id' => UserMemberService::ToInfo($val['user_id'],'user_nickname',-1).'&nbsp;&nbsp;(&nbsp;<small>'.$val['user_id'].'</small>&nbsp;)',
                    'coin' => $val['coin'],
                    'invite_level' => $levelName,
                    'change_class_id' => PromInviteBonusService::typeList($val['change_class_id']),
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

}
