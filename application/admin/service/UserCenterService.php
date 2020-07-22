<?php
namespace app\admin\service;

use app\admin\model\UserCenterModel;
use app\admin\model\UserMessageModel;
use think\Exception;
use think\Log;

class UserCenterService extends BaseService
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
            $model = new UserCenterModel();
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
            $model = new UserCenterModel();
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
     * 消息
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function messageList($filter)
    {
        //条件处理
        $admin_id = cmf_get_current_admin_id();
        $where = "status=1 and receive_id ={$admin_id}";

        //排序字段
        $sort = "id desc";

        if(isset($filter['read_flag'])){
            $where .= " and read_flag = {$filter['read_flag']}";
        }

        //返回字段
        $field = '*';

        //join表
        $join = [];

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;
        //page
        //$limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 3;
        //$limit['pageSize'] = 3;

        //调用模型 处理
        $model = new UserMessageModel();
        $result = $model->selectAll($where, $join, $field = '', $order = '', $limit);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

}