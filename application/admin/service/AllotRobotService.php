<?php
namespace app\admin\service;

use app\admin\model\AllotRobotModel;
use think\Exception;
use think\Log;

class AllotRobotService extends BaseService
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
                $where .= " and (u.id = {$filter['keywords']} or a.id = {$filter['keywords']})  ";
            }else{
                $where .= " and u.user_nickname like '%{$filter['keywords']}%' ";
            }

        }
        //性别
        if(is_numeric($filter['sex'])){
            $where .= " and u.sex = {$filter['sex']}";
        }
        //所属客服
        if(!empty($filter['user_id'])){
            $where .= " and a.custom_id = {$filter['user_id']}";
        }

         //是否达人
        if(is_numeric($filter['virtual_pos'])){
            if($filter['virtual_pos'] == 1){
                $where .= " and u.virtual_pos = 1";
            }else if($filter['virtual_pos'] == 0){
                $where .= " and u.virtual_pos = 0";
            }
        }else{
            $where .= " and u.daren_status >= 0";
        }

        //是否达人
        if(is_numeric($filter['daren_status'])){
            if($filter['daren_status'] == 2){
                $where .= " and u.daren_status = 2";
            }else if($filter['daren_status'] == 0){
                $where .= " and u.daren_status != 2";
            }
        }else{
            $where .= " and u.daren_status >= 0";
        }

        //是否vip
        if (is_numeric($filter['is_vip']) && $filter['is_vip' ] >= 0)
        {
            $time = time();
            if($filter['is_vip'] == 1){
                $where .= " and u.vip_expire_time > {$time}";
            }else{
                $where .= " and u.vip_expire_time < {$time}";
            }
        }
        //echo $where;die;
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
        $sort_field = [1=>'id',2=>'id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.robot_id'];
        //返回字段
        $field = 'a.*, u.user_login, u.id user_id, u.user_nickname, u.avatar,u.mobile,u.sex,u.user_status,u.daren_status,u.vip_expire_time,u.virtual_pos';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new AllotRobotModel();
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
            $model = new AllotRobotModel();
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
                $model = new AllotRobotModel();
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
            $model = new AllotRobotModel();

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
     * 类型配置
     * @param $key int
     * @return array
     */
    public static function typeList($key=-1){
        $aRet = [
            1 => '审批中',
            2 => '审批通过打款中',
            3 => '已打款',
            10 => '审批未通过',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }
}