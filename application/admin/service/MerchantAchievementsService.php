<?php
namespace app\admin\service;

use app\admin\model\MerchantCommonModel;
use app\admin\model\PromInviteBonusModel;
use think\Exception;

class MerchantAchievementsService extends BaseService
{

    /**
     * 列表
     * @param $filter array 接收参数
     * @param $user_id string 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function RList($filter,$user_id)
    {
        //条件处理
        $where = "a.invite_user_id in($user_id)";
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and a.invite_user_id = {$filter['keywords']} ";
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
        $sort_field = [0=>'id',1=>'invite_user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //echo $where;die;

        //连表
        $join = ['a','user u1', 'u1.id=a.invite_user_id'];

        //返回字段
        $field = 'a.*, u1.user_nickname u1_nickname, u2.user_nickname u2_nickname';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new PromInviteBonusModel();
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

}