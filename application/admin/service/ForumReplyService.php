<?php
namespace app\admin\service;

use app\admin\model\ForumReplyModel;
use think\Exception;
use think\Log;

class ForumReplyService extends BaseService
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
            $model = new ForumReplyModel();
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
                $model = new ForumReplyModel();
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
    public static function UpdateB($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new ForumReplyModel();
            $res = $model->UpdateOne($where,$condition);
            if($res) {
                return $res;
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('修改数据失败');
        }

    }


    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function statusList($key=-1){
        $aRet = [
            2 => '正常',
            0 => '已删除',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '未知';
        }
        return $aRet;
    }


    /**
     * 获取表字段信息 多数据
     * @param  $where array|string
     * @param  $field string
     * @param  $sort string
     * @return array
     * @throws
     */
    static function getTableInfo($where = "status=1", $field = '*', $sort = 'id desc'){
        //调用模型 处理
        $model = new ForumReplyModel();
        $result = $model->getInfo($where,$field,$sort);
        if($result){
            return $result;
        }else{
            return [];
        }
    }

}