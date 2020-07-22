<?php
namespace app\admin\service;

use app\admin\model\VipOrderModel;
use think\Exception;

class VipOrderService extends BaseService
{
    /**
     * 用户反馈列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function RList($filter)
    {
        //条件处理
        $where = '1=1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.order_no like '%{$filter['keywords']}%' or u.mobile like '%{$filter['keywords']}%' or u.id = {$filter['keywords']}) ";
        }

        //充值渠道
        $pay_channel = cmf_get_option('pay_conf')['channel'];
        if (isset($filter['pay_channel']) && isset($pay_channel[$filter['pay_channel']]))
        {
            $where .= " and a.pay_channel = '{$filter['pay_channel']}'";
        }

        //状态
        if (isset($filter['status']) && in_array($filter['status'],[0,1,2,10]))
        {
            $where .= " and a.status = {$filter['status']}";
        }

        //搜索提交时间
        if(!empty($filter['start_time']))
        {
            $where .= " and a.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and a.create_time <= {$filter['end_time']}";
        }

        //排序字段
        if(!empty($filter['sortField']) && !empty($filter['sortType']))
        {
            $sort = "a.{$filter['sortField']} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //返回字段
        $field = 'a.*,u.id uid, u.user_nickname, u.mobile, u.age, u.sex, u.last_login_time, u.last_login_ip';

        //join表
        $join = ['a', 'user u', 'u.id = a.user_id'];

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new VipOrderModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 状态配置
     * @param $key int
     * @return array
     */
    public static function statusListSelect($key)
    {
        $aRet = [
            0 => '未支付',
            1 => '支付中',
            2 => '支付成功',
           10 => '支付失败',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }

    /**
     * 状态配置
     * @param $param string|int
     * @return array
     */
    public static function channelListSelect($param)
    {
        $pay_conf = cmf_get_option('pay_conf')['channel'];
        $aRet = [];
        if(count($pay_conf) > 0)
        {
            foreach ($pay_conf as $key=>$val){
                switch ($val)
                {
                    case 'wxpay' :
                        $aRet['wxpay'] = '支付宝';
                        break;
                    case 'alipay':
                        $aRet['alipay'] = '微信';
                        break;
                    default:
                        break;
                }
            }
        }


        if(count($aRet)>0 && in_array($param,$pay_conf)){
            return $aRet[$param];
        }

        return $aRet;
    }

    /**
     * 单数据查询
     * @param $id int
     * @throws Exception
     * @return array|string
     */
    public static function ToInfo($id)
    {
        try{
            $where = ['a.id'=>$id];
            $field = 'a.*,u.user_nickname, u.mobile';
            $join = ['a', 'user u', 'u.id = a.user_id'];
            //调用模型 处理
            $model = new VipOrderModel();
            $data = $model->selectOne($where,$join,$field);
            if($data)
            {
                return $data;
            }else{
                return '';
            }
        }catch (Exception $exception)
        {
            throw new Exception($exception->getMessage());
        }
    }
}