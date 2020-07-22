<?php
namespace app\admin\service;

use app\admin\model\MerchantCommonModel;
use think\Db;
use think\Exception;
use think\Log;

class MerchantUserService extends BaseService
{

    /**
     * 列表
     * @param $filter array 接收参数
     * @param $user_id string 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function UList($filter, $user_id)
    {
        //条件处理
        $where = "a.status=1 and a.user_id in($user_id)";
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and (u.id = {$filter['keywords']} or u.mobile = {$filter['keywords']})  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and u.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and u.create_time <= {$filter['end_time']}";
        }


        //排序字段
        $sort_field = [0=>'a.id'];
        if(isset($filter['sortField']) && isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "u.id desc";
        }

        //返回字段
        $field = 'u.*';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //join
        $join = [
            ['merchant_management m','m.id = a.m_id'],
            ['user u','u.id = a.user_id'],
        ];

        //调用模型 处理
        $model = new MerchantCommonModel();
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
            $model = new MerchantCommonModel();
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
     * @param $filter
     * @param $user_id
     * @return array
     * @throws
     */
    public static function getInviteUser($filter,$user_id){
        //条件处理
        $where = "a.invite_user_id in({$user_id})";
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and (u.id = {$filter['keywords']} or u.mobile = {$filter['keywords']})  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and u.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and u.create_time <= {$filter['end_time']}";
        }


        //排序字段
        $sort_field = [0=>'a.id'];
        if(isset($filter['sortField']) && isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //返回字段
        $field = 'u.*';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $sql = Db::name('user_invite_relation')
                ->alias('a')
                ->join('user u','u.id=a.beinvite_user_id')
                ->where($where)
                ->field($field)
                ->order($sort)
                ->limit($limit['offset'],$limit['pageSize']);
        //分页数
        $total_sql = clone $sql;
        $total = $total_sql->count();
        //返回数组
        $data = $sql->select()->toArray();
        $result = [
            'total'=>$total,
            'data'=>$data,
        ];
        if($result){
            return $result;
        }else{
            return [];
        }
    }
}