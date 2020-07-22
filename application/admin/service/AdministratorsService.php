<?php
namespace app\admin\service;

use app\admin\model\RoleModel;
use app\admin\model\UserMemberModel;
use think\Exception;
use think\Log;

class AdministratorsService extends BaseService
{
    /**
     * 礼物列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function RList($filter)
    {
        //条件处理
        $where = 'user_type=1';
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and ( id = {$filter['keywords']}) ";
        }

        //状态
        $where .= " and user_status >= 0";


        //搜索提交时间
        if(!empty($filter['start_time']))
        {
            $where .= " and create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and create_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_filed = [1=>'id'];
        if(isset($sort_filed[$filter['sortField']]))
        {
            $sort = "{$sort_filed[$filter['sortField']]} {$filter['sortType']}";
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
        $model = new UserMemberModel();
        $result = $model->selectAll($where, [], $field, $sort, $offset, $pageSize);
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
            $model = new UserMemberModel();
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
                $model = new UserMemberModel();
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
            $model = new UserMemberModel();
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
    public static function statusList($key = -1)
    {
        $aRet = [
            0 => '未认证',
            1 => '认证中',
            2 => '认证通过',
            10 => '认证失败',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }

    /**
     * 获取角色信息
     * @return array|bool
     */
    public static function getRole($where = 'status = 1'){
        $model = new RoleModel();
        $res = $model->selectAll($where,[],'name,id');
        if($res){
            return $res;
        }else{
            return [];
        }
    }
}