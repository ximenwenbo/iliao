<?php
namespace app\admin\service;

use app\admin\model\RechargeOrderModel;
use app\admin\model\RechargeOrderModelModel;
use think\Exception;
use think\Log;

class RechargeOrderService extends BaseService
{
    /**
     * 充值订单列表
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
            $where .= " and (a.user_id like '%{$filter['keywords']}%' or u.mobile like '%{$filter['keywords']}%')  ";
        }

        //状态
        if (isset($filter['status']) && is_numeric($filter['status']))
        {
            $where .= " and a.status = {$filter['status']}";
        }else{
            $where .= " and a.status >= 0";
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
        $sort_field = [1=>'id', 2 => 'user_id'];
        if(isset($filter['sortField']) && isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.user_id'];
        //返回字段
        $field = 'a.*, u.id user_id, u.user_nickname, u.mobile';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new RechargeOrderModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 单数据查询
     * @param $where array|int|string
     * @param $field string
     * @param $type int
     * @throws Exception
     * @return array|string
     */
    public static function ToInfo($where,$field = '*',$type = 0)
    {
        try{
            if(is_numeric($where)){
                $where = ['id'=>$where];
            }
            //调用模型 处理
            $model = new RechargeOrderModel();
            if(empty($type)){
                $data = $model->selectOne($where,$field);
            }else{
                $data = $model->selectOne($where,$field,1);
            }

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

    /**
     * 添加数据
     * @param $condition array 条件
     * @throws Exception
     * @return int
     */
    public static function AddData($condition)
    {
        try{
            if(is_array($condition))
            {
                $model = new RechargeOrderModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
                }else{
                    Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($insert_id, true)),'error');
                    throw new Exception('新增数据失败');
                }
            }

        }catch (Exception $exception)
        {
            Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('新增数据失败');
        }
    }

    /**
     * 更新数据
     * @param $where array
     * @param $condition array
     * @throws Exception
     * @return int
     */
    public static function UpdateB($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new RechargeOrderModel();
            $res = $model->UpdateOne($where,$condition);
            if($res){
                return $res;
            }else{
                throw new Exception('修改数据失败');
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('修改数据失败');
        }

    }


    /**
     * 状态配置
     * @param $key int
     * @return array
     */
    public static function statusList($key=-1){
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
     * 渠道配置
     * @param $key int
     * @return array|string
     */
    public static function channelList($key=-1){
        $aRet = [
            'alipay' => '支付宝',
            'wxpay' => '微信',
            'applepay' => '苹果支付',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '未知';
        }
        return $aRet;
    }


    /**
     * 充值订单列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function URList($filter)
    {
        //条件处理
        $where = 'a.status=2';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.user_id like '%{$filter['keywords']}%' or u.mobile like '%{$filter['keywords']}%')  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and a.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and a.create_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_field = [1=>'id', 2 => 'user_id'];
        if(isset($filter['sortField']) && isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.user_id'];
        //返回字段
        $field = 'a.*, u.id user_id, u.user_nickname, u.mobile';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new RechargeOrderModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

}