<?php
namespace app\admin\service;

use app\admin\model\SMSCodeModel;
use think\Exception;
use think\Log;

class SMSCodeService extends BaseService
{

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
            $model = new SMSCodeModel();
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
     * 列表
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
            $where .= " and (mobile = '{$filter['keywords']}')  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and update_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and update_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_filed = [0=>'id'];
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
        $model = new SMSCodeModel();
        $result = $model->selectAll($where,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

}