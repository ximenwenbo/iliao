<?php
namespace app\admin\service;

use app\admin\model\ResourcesModel;
use think\Exception;
use think\Log;

class HeadAuditsService extends BaseService
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
        $where = 'class_id=6';
        //关键词
        if(!empty($filter['keywords'])){
            if (is_numeric($filter['keywords']))
            {
                $where .= " and (a.user_id = {$filter['keywords']} or a.id like '%{$filter['keywords']}%') ";
            }else{
                $where .= " and (u.user_nickname like '%{$filter['keywords']}%') ";
            }
        }


        //状态
        if (!empty($filter['status']))
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
        $sort_field = [0=>'id', 1 => 'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
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
        $model = new ResourcesModel();
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
            0 => '选择审核状态',
            1 => '审核中',
            2 => '审核通过',
            10 => '审核未通过',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }


    /**
     * 单数据
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
            $model = new ResourcesModel();
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
                $model = new ResourcesModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
                }else{
                    Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($insert_id, true)),'error');
                    throw new Exception('新增数据失败');
                }
            }else{
                return false;
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
            $model = new ResourcesModel();
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