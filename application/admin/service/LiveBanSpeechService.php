<?php
namespace app\admin\service;

use app\admin\model\LiveBanSpeechModel;
use think\Exception;
use think\Log;

class LiveBanSpeechService extends BaseService
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
                $where .= " and (a.live_uid = {$filter['keywords']})  ";
            }else{
                $where .= " and u.user_nickname like '%{$filter['keywords']}%' ";
            }

        }

        //状态
        if (!empty($filter['type']) && is_numeric($filter['type']))
        {
            $where .= " and a.type = {$filter['type']}";
        }

        //搜索开始和结束时间
        if(!empty($filter['start_time']) && !empty($filter['end_time']))
        {
            if(strtotime($filter['start_time']) && strtotime($filter['end_time'])){
                $end_time = date("Y-m-d H:i:s",strtotime($filter['end_time']) + 86399);
                $where .= " and a.operate_time between '{$filter['start_time']}' and '{$end_time}'";
            }

        }

        //排序字段
        $sort_field = ['id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $order = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $order = "a.id desc";
        }

        //连表
        $join = ['user u','u.id=a.live_uid'];
        //返回字段
        $field = 'a.*,u.user_nickname';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 20;

        //调用模型 处理
        $model = new LiveBanSpeechModel();
        $result = $model->selectAll($where, $join, $field, $order, $limit);
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
            $model = new LiveBanSpeechModel();
            if(empty($type)){
                $data = $model->selectOne($where,$field);
            }else{
                $data = $model->selectOne($where,$field,1);
            }

            if($data || $data == 0)
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
                $model = new LiveBanSpeechModel();
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
            $model = new LiveBanSpeechModel();

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
     * 删除数据
     * @param $param array
     * @return int|string|object
     * @throws
     */
    public static function DeleteInfo($param){
        try{
            if (empty($param) || !is_array($param)){
                throw new Exception('参数错误');
            }
            $model = new LiveBanSpeechModel();

            $res = $model->DeleteOne($param);
            if($res){
                return $res;
            }else{
                throw new Exception('删除数据失败'.$res);
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：删除数据失败：%s', __METHOD__, var_export($param, true)),'error');
            throw new Exception('删除数据失败'.$exception->getMessage());
        }
    }

    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function typeList($key = -1){
        $aRet = [
            1 => '本场禁言',
            2 => '永久禁言',
        ];
        if($key >= 0){
            return isset($aRet[$key]) ? $aRet[$key] : '状态有误';
        }
        return $aRet;
    }



}