<?php
namespace app\admin\service;

use app\admin\model\GiftModel;
use app\admin\model\VipOrderModel;
use think\Exception;

class GiftService extends BaseService
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
            $where .= " and (name like '%{$filter['keywords']}%') ";
        }
        //使用场景
        if (!empty($filter['type']))
        {
            $where .= " and (type = {$filter['type']}) ";
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

        //排序字段
        $sort_filed = [0=>'id',6=>'coin','1'=> 'sort'];
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
        $model = new GiftModel();
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
            $model = new GiftModel();
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
        $model = new GiftModel();
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
    public static function AddGift($condition)
    {
        try{
            if(is_array($condition))
            {
                $model = new GiftModel();
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
    public static function UpdateGift($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new GiftModel();
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
    public static function typeList($key=-1){
        $aRet = [
            1 => '社区礼物',
            2 => '直播礼物',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '类型有误';
        }
        return $aRet;
    }

    /**
     * 礼物样式
     * @param $key int
     * @return array|string
     */
    public static function styleList($key=-1){
        $aRet = [
            1 => '图片',
            2 => 'gif动图',
            3 => 'svga动图',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '类型有误';
        }
        return $aRet;
    }

}