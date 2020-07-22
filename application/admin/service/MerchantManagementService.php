<?php
namespace app\admin\service;

use app\admin\model\MerchantManagementModel;
use think\Exception;
use think\Log;

class MerchantManagementService extends BaseService
{

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
            $model = new MerchantManagementModel();
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
                $model = new MerchantManagementModel();
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
            $model = new MerchantManagementModel();
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
            0 => '未使用',
            1 => '使用中',
        ];
        if($key>=0){
            return $aRet[$key];
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
    public static function UList($filter)
    {
        //条件处理
        $where = 'status=1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (name like '%{$filter['keywords']}%')  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and create_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_field = [0=>'id'];
        if(isset($filter['sortField']) && isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "id desc";
        }

        //返回字段
        $field = '*';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new MerchantManagementModel();
        $result = $model->selectAll($where,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 获取商户名称和id
     * @param  $where array|string
     * @param  $field string
     * @param  $sort string
     * @return array
     * @throws
     */
    static function getManagementInfo($where = "status=1", $field = '*', $sort = 'id desc'){
        //调用模型 处理
        $model = new MerchantManagementModel();
        $result = $model->getInfo($where,$field,$sort);
        if($result){
            return $result;
        }else{
            return [];
        }
    }

}