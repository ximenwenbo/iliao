<?php
namespace app\admin\service;

use app\admin\model\SendeUserMessagesModel;
use think\Exception;
use think\Log;

class SendUserMessagesService extends BaseService
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
        $where = '1=1';
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and ( a.receive_id like '%{$filter['keywords']}%' or a.sender_id like '%{$filter['keywords']}%' or a.id like '%{$filter['keywords']}%')  ";
        }

        //状态
        if (isset($filter['status']) && is_numeric($filter['status']))
        {
            $where .= " and a.status = {$filter['status']}";
        }else{
            $where .= " and a.status >= 0";
        }

        //
        if (is_numeric($filter['type']))
        {
            $where .= " and a.type = {$filter['type']}";
        }else{
            $where .= " and a.type >= 0";
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
        $sort_field = [0 =>'id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //返回字段
        $field = 'a.*, u1.user_nickname u1_nickname, u2.user_nickname u2_nickname';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //join
        $sender_id_join =  ['user u1','u1.id = a.sender_id'];
        $receive_id_join = ['user u2','u2.id = a.receive_id'];

        //调用模型 处理
        $model = new SendeUserMessagesModel();
        $result = $model->selectAll($where,$sender_id_join,$receive_id_join,$field,$sort,$offset,$pageSize);
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
            $model = new SendeUserMessagesModel();
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
                $model = new SendeUserMessagesModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
                }else{
                    return 0;
                }
            }

        }catch (Exception $exception) {
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
            $model = new SendeUserMessagesModel();
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
     * 类型配置
     * @param $key int
     * @return array
     */
    public static function typeList($key=-1){
        $aRet = [
            1 => '后台',
            2 => 'APP端',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }
}