<?php
namespace app\admin\service;

use app\admin\model\PromInviteRelaModel;
use think\Exception;
use think\Log;

class PromInviteRelaService extends BaseService
{
    /**
     * 客服业绩列表
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
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and (a.user_id = {$filter['keywords']} or a.parent_uid = {$filter['keywords']})";
        }


        //搜索开始和结束时间
        if(!empty($filter['start_time']) && !empty($filter['end_time']))
        {
            $start_time = date("Y-m-d H:i:s",$filter['start_time']);
            $end_time = date("Y-m-d H:i:s",$filter['end_time']);
            $where .= " and a.create_time between '{$start_time}' and '{$end_time}'";
        }


        //排序字段
        $sort_field = [0=>'id',1=>'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }


        //连表
        $join = ['user u', 'u.id=a.user_id'];

        //返回字段
        $field = 'a.*, u.user_nickname';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new PromInviteRelaModel();
        $result = $model->selectAll($where,$join,$field,$sort,$limit);
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
            $model = new PromInviteRelaModel();
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
    public static function AddInfo($condition)
    {
        try{
            if(is_array($condition))
            {
                $model = new PromInviteRelaModel();
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
    public static function UpdateInfo($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new PromInviteRelaModel();
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

}