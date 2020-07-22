<?php
namespace app\admin\service;

use app\admin\model\AppVersionModel;
use think\Exception;

class AppVersionService extends BaseService
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
        if (!empty($filter['keywords']))
        {
            $where .= " and (app_version like '%{$filter['keywords']}%') ";
        }

        //状态
        if (isset($filter['status']) && !empty($filter['status']))
        {
            $where .= " and status = {$filter['status']}";
        }else{
            $where .= " and status >= 0";
        }

        //搜索提交时间
        if(!empty($filter['start_time']))
        {
            $where .= " and create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and create_time <= {$filter['end_time']}";
        }

        //强制更新
        if($filter['update_status'] > 0){
            $where .= " and update_status = {$filter['update_status']}";
        }
        //系统
        if($filter['system_type'] > 0){
            $where .= " and system_type = {$filter['system_type']}";
        }

        //排序字段
        $sort_filed = [1=>'id',4=>'app_version'];
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
        $model = new AppVersionModel();
        $result = $model->selectAll($where,$field,$sort,$offset,$pageSize);
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
            $model = new AppVersionModel();
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
     * 生成随机唯一码并验证唯一性
     * @param $length int 长度
     * @param $chars string
     * @return string
     */
    public static function RandomUniqueCode($length = 7, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $code = '', $lc = strlen($chars)-1; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, $lc)];
        }
        $model = new AppVersionModel();
        if($model->selectOne(['uni_code'=>$code],'id',1)){
            self::RandomUniqueCode($length,$chars);
        }
        return $code;
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
                $model = new AppVersionModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
                }else{
                    Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($insert_id, true)),'error');
                    throw new Exception('新增数据失败');
                }
            }else{
                return false;
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
            $model = new AppVersionModel();
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

}