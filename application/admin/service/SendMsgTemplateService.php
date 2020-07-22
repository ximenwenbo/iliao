<?php
namespace app\admin\service;


use app\admin\model\SendMsgTemplateModel;
use think\Exception;
use think\Log;

class SendMsgTemplateService extends BaseService
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
        $where = '1 = 1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and ( tmp_code like '%{$filter['keywords']}%' or content like '%{$filter['keywords']}%') ";
        }

        //搜索提交时间
        if(!empty($filter['start_time']))
        {
            $where .= " and create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and create_time <= {$filter['end_time']}";
        }

        //状态
        if (isset($filter['status']))
        {
            $where .= " and status = {$filter['status']}";
        }else{
            $where .= " and status >= 0";
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

        $join = [];
        //调用模型 处理
        $model = new SendMsgTemplateModel();
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
            $model = new SendMsgTemplateModel();
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
                $model = new SendMsgTemplateModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
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
    public static function UpdateInfo($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new SendMsgTemplateModel();
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
            1 => '系统消息',
            10 => '单聊消息',
        ];
        if($key>=0){
            return isset($aRet[$key]) ?  $aRet[$key] : '类型错误';
        }
        return $aRet;
    }

    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function statusList($key = -1)
    {
        $aRet = [
            1 => '<span style="color:green;">正常</span>',
            2 => '<span style="color:red;">禁用</span>',
        ];
        if($key>=0){
            return isset($aRet[$key]) ?  $aRet[$key] : '状态错误';
        }
        return $aRet;
    }
}