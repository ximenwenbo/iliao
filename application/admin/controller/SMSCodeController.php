<?php
/**
 * 手机验证码管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\SMSCodeService;
use cmf\controller\AdminBaseController;
use think\Db;

class SMSCodeController extends AdminBaseController
{
    /**
     * 验证码列表
     * @author zjy
     * @throws
     */
    public function index()
    {
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
            'class_id' => empty($params['data']['class_id']) ? 1 : $params['data']['class_id'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => !isset($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = SMSCodeService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '<a style="color: #fff;" href="/admin/pay_trade/index/keywords/'.$val['id'].'" target="_blank"><button type="button" class="btn btn-success btn-sm">
                            <i class="icon wb-info-circle" aria-hidden="true"></i> 用户充值详情
                         </button></a>';
                $filed =  [
                    'id' => $val['id'],
                    'mobile' => $val['mobile'],
                    'code' => $val['code'],
                    "update_time"=> date("Y-m-d H:i",$val['update_time']),
                    "expire_time"=> date("Y-m-d H:i",$val['expire_time']),
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
