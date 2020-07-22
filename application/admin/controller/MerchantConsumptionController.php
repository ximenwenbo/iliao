<?php
/**
 * 消费管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MerchantConsumptionService;
use app\admin\service\MerchantCustomerService;
use app\admin\service\UserCoinService;
use cmf\controller\AdminBaseController;
use think\Db;


class MerchantConsumptionController extends AdminBaseController
{
    /**
     * 列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
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

            $user_id = $this->getUserId();
            $result = MerchantConsumptionService::UList($condition,$user_id);
            //var_dump($result);die;
            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $opera = '<button type="button" class="btn btn-success btn-sm" onclick="detailsPopup('.$val['id'].')">
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
        $merchant_name = MerchantCustomerService::getMerchantName();
        $this->assign('merchant_name', $merchant_name);
        return $this->fetch();

    }

    /**
     * @return bool|string|array
     * @throws
     */
    public function getUserId(){
        $admin_id = cmf_get_current_admin_id();
        $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
        if($m_id === NULL){
            if($admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                exit('您没有权限查看,请联系超级管理员!');
            }
        }else{
            if($m_id == 0 && $admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
                $uid_arr = Db::name('merchant_customer')->where("m_id = {$m_id} and status=1")->field('user_id')->select()->toArray();
            }
        }

        if(!$uid_arr){
            exit("商户下没有客服");
        }
        $uid_in = '';
        foreach ($uid_arr as $k1 => $v1){
            $uid_in .= $v1['user_id'].',';
        }
        $uid_in = substr($uid_in,0,strlen($uid_in)-1);

        $res = Db::name('merchant_customer')
            ->alias('a')
            ->join('user_invite_relation r','r.invite_user_id = a.user_id')
            ->where("a.status=1 and a.user_id in($uid_in)")
            ->field('r.beinvite_user_id')
            ->select()->toArray();

        if(!$res){
            exit('商户没有邀请会员');
        }
        $be_user_id = '';
        foreach ($res as $k1 => $v1){
            $be_user_id .= $v1['beinvite_user_id'].',';
        }
        $be_user_id = substr($be_user_id,0,strlen($be_user_id)-1);
        return $be_user_id;
    }
}
