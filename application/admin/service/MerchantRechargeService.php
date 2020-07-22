<?php
namespace app\admin\service;

use app\admin\model\MerchantCommonModel;
use app\admin\model\PayTradeModel;
use think\Exception;

class MerchantRechargeService extends BaseService
{

    /**
     * 列表
     * @param $filter array 接收参数
     * @param $user_id string 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function UList($filter,$user_id)
    {
        //条件处理
        $where = "a.status=2 and a.user_id in($user_id) ";
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and (a.user_id = {$filter['keywords']} )  ";
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
        $model = new PayTradeModel();
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

}