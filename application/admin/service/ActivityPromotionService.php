<?php
namespace app\admin\service;

use app\admin\model\ActivityPromotionModel;
use think\Exception;
use think\Log;

class ActivityPromotionService extends BaseService
{
    /**
     * 机器人列表
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
            if(is_numeric($filter['keywords'])){
                $where .= " and (id = {$filter['keywords']})  ";
            }else{
                $where .= " and (title like '%{$filter['keywords']}%' )";
            }

        }

        //直播间id
        if (!empty($filter['live_id']) && is_numeric($filter['live_id']))
        {
            $where .= " and (live_id = {$filter['live_id']})  ";
        }

        //状态
        if (!empty($filter['status']) && is_numeric($filter['status']))
        {
            $where .= " and status = {$filter['status']}";
        }else{
            $where .= " and status >= 0";
        }

        //搜索开始和结束时间
        if(!empty($filter['start_time']) && !empty($filter['end_time']))
        {
            $start_time = $filter['start_time'];
            $end_time = $filter['end_time'];
            $where .= " and create_time between '{$start_time}' and '{$end_time}'";
        }

        //排序字段
        $sort_field = [0=>'id',1=>'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $order = "{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $order = "id desc";
        }

        //连表
        $join = [];
        //返回字段
        $field = '*';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 20;

        //调用模型 处理
        $model = new ActivityPromotionModel();
        $result = $model->selectAll($where, $join, $field, $order, $limit);
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
            $model = new ActivityPromotionModel();
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
                $model = new ActivityPromotionModel();
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
     * @param $where array|string
     * @param $condition array
     * @throws Exception
     * @return int
     */
    public static function UpdateB($where, $condition)
    {
        try{
            if (empty($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new ActivityPromotionModel();

            $res = $model->UpdateOne($where,$condition);
            if($res){
                return $res;
            }else{
                throw new Exception('修改数据失败'.$res);
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('修改数据失败'.$exception->getMessage());
        }

    }

    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function statusList($key = -1){
        $aRet = [
            1 => '<span style="color:#3e8ef7;">使用</span>',
            2 => '<span style="color:#c6d3d7;">停用</span>',
        ];
        if($key >= 0){
            return isset($aRet[$key]) ? $aRet[$key] : '状态有误';
        }
        return $aRet;
    }

    /**
     * 推送类型配置
     * @param $key int
     * @return array|string
     */
    public static function typeList($key = -1){
        $aRet = [
            1 => '个人主页',
            2 => 'H5',
        ];
        if($key >= 0){
            return isset($aRet[$key]) ? $aRet[$key] : '类型有误';
        }
        return $aRet;
    }

    /**
     * 推送类型配置
     * @param $key int
     * @return array|string
     */
    public static function PushTypeList($key = -1){
        $aRet = [
            1 => '推送一次',
            2 => '每日一次',
            3 => '每周一次',
            4 => '每月一次',
        ];
        if($key >= 0){
            return isset($aRet[$key]) ? $aRet[$key] : '类型有误';
        }
        return $aRet;
    }

}