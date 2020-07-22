<?php
/**
 * 充值金币记录管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\PayTradeService;
use app\admin\service\RechargeOrderService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;


class RechargeOrderController extends AdminBaseController
{
    /**
     * 充值记录列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $status = RechargeOrderService::statusList();
        $this->assign('status', $status);
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
            'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = RechargeOrderService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                //操作按钮
                $opera = '<button type="button" class="btn btn-outline btn-danger btn-sm " id="Del">
                            <i class="icon wb-warning" aria-hidden="true"></i> 删除
                         </button>';

                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'status' => RechargeOrderService::statusList($val['status']),
                    'user_id' => $val['user_id'],
                    'mobile' => $val['mobile'],
                    'user_nickname' => $val['user_nickname'],
                    'order_no' => !empty($val['order_no']) ? $val['order_no']:'无',
                    'pay_channel' => RechargeOrderService::channelList($val['pay_channel']),
                    'amount' => $val['amount']/100,
                    'coin' => $val['coin'],
                    'subject' => $val['subject'],
                    'extra_id' =>  !empty($val['extra_id']) ? $val['extra_id'] : '无',
                    'error_msg' => !empty($val['error_msg']) ?  $val['error_msg'] : '无',
                    'finish_time' => !empty($val['finish_time']) ? date("Y-m-d H:i",$val['finish_time']) : '无',
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
                    "update_time"=> !empty($val['update_time']) ? date("Y-m-d H:i",$val['update_time']) : '无',
                    'opera' => $opera
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

    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DelInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(['msg'=>'数据不存在','code'=>0]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
        ];
        $result = RechargeOrderService::UpdateB(['id'=>$id],$condition);
        if($result){
            return json_encode(['msg'=>'删除成功','code'=>200]);
        }else{
            return json_encode(['msg'=>'删除失败','code'=>0]);
        }

    }

}
