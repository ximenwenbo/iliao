<?php
/**
 * Vip订单管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use app\admin\service\ResourcesService;
use app\admin\service\txyun\YuntongxinService;
use app\admin\service\VipOrderService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class VipOrderController extends AdminBaseController
{
    /**
     * 订单列表
     * @author zjy
     * @throws
     */
    public function index()
    {

        $status = VipOrderService::statusListSelect(-1);
        $pay_conf = VipOrderService::channelListSelect(-1);
        $this->assign('status',$status);
        $this->assign('pay_conf',$pay_conf);
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
            'user_id' => empty($params['data']['userId']) ? 0 : $params['data']['userId'],
            'status' => empty($params['data']['status']) ? -1 : $params['data']['status'],
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pay_channel' => empty($params['data']['pay_channel']) ? 1 : $params['data']['pay_channel'],
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => 'id',
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = VipOrderService::RList($condition);
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $sex = ['保密','男','女'];
                $type = ['','月卡','季卡','年卡'];
                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'order_no' => $val['order_no'],
                    'uid' => $val['uid'],
                    'nickname' => $val['user_nickname'],
                    'pay_channel' => VipOrderService::channelListSelect($val['pay_channel']),
                    'type' => $type[$val['type']],
                    'amount' => $val['amount']/100,
                    'subject' => $val['subject'],
                    'status' => VipOrderService::statusListSelect($val['status']),
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
                    "finish_time"=> date("Y-m-d H:i",$val['create_time']),
                    'remark'=> $val['remark'],
                    'mobile' => !empty($val['mobile']) ? $val['mobile'] : '无',
                    'age' => !empty($val['age']) ? $val['age'] : '无',
                    'sex' => !empty($val['sex']) ? $sex[$val['sex']] : '无',
                    'last_login_time' => !empty($val['last_login_time']) ? date("Y-m-d H:i",$val['last_login_time']) : '无',
                    'last_login_ip' => !empty($val['last_login_ip']) ? $val['last_login_ip'] : '无',
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
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function authDelete()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在",]);
        }
        $condition = [
            'id' => $id,
            'status' => -99,
            'update_time' => time(),
        ];
        $result = Db::name('oss_material')->update($condition);
        if(empty($result))
        {
            return json_encode(["status"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["status"=>200, "msg"=>"删除成功!",]);
        }

    }

}
