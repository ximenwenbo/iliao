<?php
namespace app\admin\service;


use app\admin\model\TopicCircleModel;
use think\Db;
use think\Exception;


class PostAcquisitionService extends BaseService
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
        $where = "1=1";
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.account_uuid like '%{$filter['keywords']}%' or u.nickname like '%{$filter['keywords']}%' )";
        }

        if (is_numeric($filter['cj_status']))
        {
            $where .= " and a.cj_status = {$filter['cj_status']}";
        }
        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and a.trans_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and a.trans_time <= {$filter['end_time']}";
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
        $field = 'a.*, u.nickname,u.avatar';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //join
        $join = ['a','t_jiaoliuqu_account u','u.account_uuid = a.account_uuid'];

        //调用模型 处理
        $model = new TopicCircleModel();
        $result = $model->selectAll($where, $join, $field, $sort,$limit['offset'], $limit['pageSize']);
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
    public static function statusList($key = -1)
    {
        $aRet = [
            0 => '未采用',
            1 => '已采用',
            5 => '同步成功',
            10 => '同步失败',
            -99 => '不采用',
        ];
        if($key>=0 || $key == -99){
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