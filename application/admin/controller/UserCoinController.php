<?php
/**
 * 消费记录管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\UserCoinService;
use cmf\controller\AdminBaseController;
use think\Request;


class UserCoinController extends AdminBaseController
{
    /**
     * 充值记录列表
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
            'status' => empty($params['data']['status']) ? '' : $params['data']['status'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 1 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = UserCoinService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '<button type="button" class="btn btn-success btn-outline btn-sm" onclick="detailsPopup('.$val['id'].')">
                                    <i class="icon wb-info-circle" aria-hidden="true" ></i> 详情
                                 </button>';
                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'user_id' => $val['user_id'],
                    'mobile' => $val['mobile'],
                    'coin' => $val['coin'],
                    'user_nickname' => $val['user_nickname'],
                    'change_coin' => $val['change_coin'],
                    'change_subject' => $val['change_subject'],
                    'change_class_id' => UserCoinService::changeList($val['change_class_id']),
                    'change_type' => $val['change_type'] == 1 ? '增加': '减少',
                    'coin_type' => $val['coin_type'] == 1 ? '可提现 ': '不可提现',
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
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
     * 查看详情
     * @throws
     */
    public function ViewDetails(){
        $id = $this->request->param('id');
        $coin_info = UserCoinService::ToInfo(['id'=>$id],'change_class_id,change_data_id');
        if($coin_info){
            $res = UserCoinService::detailsAbout($coin_info['change_class_id'],$coin_info['change_data_id']);
            if($res){
                $status = ['创建未进行', '进行中', '正常结束', '非正常结束'];
                $type = [1=>'文字',2=>'语音', 3=>'视频'];
                $this->assign('change_class_id', $coin_info['change_class_id']);
                $this->assign('type',$type);
                $this->assign('status',$status);
                $this->assign('info',$res);
                return $this->fetch();
            }else{
                echo '订单数据有误!';exit();
            }
        }
        echo '该订单不存在!';exit();
    }


}
