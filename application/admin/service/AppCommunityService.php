<?php
namespace app\admin\service;

use app\admin\model\AppCommunityModel;
use think\Exception;
use think\Log;

class AppCommunityService extends BaseService
{


    /**
     * 列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function UList($filter)
    {
        //条件处理
        $where = '1=1';
        if (!empty($filter['users']))
        {
            if(is_numeric($filter['users'])){
                $where .= " and (u.id = {$filter['users']})  ";
            }else{
                $where .= " and (u.user_nickname like '%{$filter['users']}%' )  ";
            }

        }
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.title like '%{$filter['keywords']}%' OR a.content like '%{$filter['keywords']}%') ";
        }

        //状态 默认正常
        if(is_numeric($filter['status'])){
            $where .= " and a.status = {$filter['status']}";
        }else{
            $where .= " and a.status = 2";
        }

        //涉黄等级 默认正常
        if(is_numeric($filter['y_level'])){
            $where .= " and a.y_level = {$filter['y_level']}";
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
        $sort = "a.id desc";


        //返回字段
        $field = 'a.*,u.user_nickname nickname,u.sex,u.avatar,u.city_name';

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //limit
        $offset = !empty($filter['current_page']) ? $filter['current_page']*$pageSize : 0;


        //join
        $user_join = ['user u','u.id = a.user_id'];

        //调用模型 处理
        $model = new AppCommunityModel();
        $result = $model->selectAll($where,$user_join,$field,$sort,$offset,$pageSize);
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
            $model = new AppCommunityModel();
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
                $model = new AppCommunityModel();
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
            $model = new AppCommunityModel();
            $res = $model->UpdateOne($where,$condition);
            if($res) {
                return $res;
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('修改数据失败');
        }

    }


    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function statusList($key=-1){
        $aRet = [
            2 => '正常',
            0 => '已删除',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '未知';
        }
        return $aRet;
    }


    /**
     * 获取表字段信息 多数据
     * @param  $where array|string
     * @param  $field string
     * @param  $sort string
     * @return array
     * @throws
     */
    static function getTableInfo($where = "status=1", $field = '*', $sort = 'id desc'){
        //调用模型 处理
        $model = new AppCommunityModel();
        $result = $model->getInfo($where,$field,$sort);
        if($result){
            return $result;
        }else{
            return [];
        }
    }

}