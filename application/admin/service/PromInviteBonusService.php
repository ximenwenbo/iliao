<?php
namespace app\admin\service;

use app\admin\model\PromInviteBonusModel;
use think\Exception;
use think\Log;

class PromInviteBonusService extends BaseService
{
    /**
     * 客服业绩列表
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
        if(!empty($filter['uuid'])){
            if($filter['uuid'] == 2){
                if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
                {
                    $where .= " and a.user_id = {$filter['keywords']} ";
                }
            }else{
                if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
                {
                    $where .= " and a.from_uid = {$filter['keywords']} ";
                }
            }
        }else{
            if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
            {
                $where .= " and (a.from_uid = {$filter['keywords']} or a.user_id = {$filter['keywords']} )";
            }
        }



        //搜索开始和结束时间
        if(!empty($filter['start_time']) && !empty($filter['end_time']))
        {
            $start_time = date("Y-m-d H:i:s",$filter['start_time']);
            $end_time = date("Y-m-d H:i:s",$filter['end_time']);
            $where .= " and a.create_time between '{$start_time}' and '{$end_time}'";
        }

        //排序字段
        $sort_field = [0=>'id',1=>'from_uid'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        if(!empty($filter['change_class_id'])){
            $where .= " and a.change_class_id = {$filter['change_class_id']}";
        }

        //连表
        $join = ['user u', 'u.id=a.from_uid'];

        //返回字段
        $field = 'a.*, u.user_nickname';

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
            $model = new PromInviteBonusModel();
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
                $model = new PromInviteBonusModel();
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
            $model = new PromInviteBonusModel();
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
     * @return array|string
     */
    public static function typeList($key = -1)
    {
        $aRet = [
            0 => '无类型',
            1 => '充值收入',
            11 => '拉新奖励',
            42 => '礼物收入',
            44 => '守护收入',
            41 => '音视频聊天',
            43 => '直播间门票',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '错误类型';
        }
        return $aRet;
    }


}