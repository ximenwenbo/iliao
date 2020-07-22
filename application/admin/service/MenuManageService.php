<?php
namespace app\admin\service;

use app\admin\model\MenuManageModel;
use think\Exception;

class MenuManageService extends BaseService
{

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
        $where = "1=1";
        //关键词
        if (!empty($filter['keywords']) && is_numeric($filter['keywords']))
        {
            $where .= " and (id = {$filter['keywords']} or parent_id = {$filter['keywords']})";
        }

        //状态
        if(is_numeric($filter['status'])){
            $where .= " and status = {$filter['status']}";
        }

        //菜单选择
        if(!empty($filter['first_class']) && is_numeric($filter['first_class']) && empty($filter['keywords'])){
            $where .= " and (id = {$filter['first_class']} or parent_id = {$filter['first_class']})";
        }

        //排序字段
        $sort_field = [0=>'id',1=>'parent_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "id desc";
        }

        //连表
        $join = [];

        //返回字段
        $field = '*';

        //limit
        $limit['offset'] = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $limit['pageSize'] = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new MenuManageModel();
        $result = $model->selectAll($where,$join,$field,$sort,$limit);
        if($result || $result == 0){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 获取一级、二级菜单id和名称
     */
    public static function getMenuTree(){
        $where = ['parent_id' => 0];
        $filed = 'name,id,parent_id';
        $model = new MenuManageModel();
        $result = $model->getMenuData($where,$filed);
        if(!empty($result)){
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
            $model = new MenuManageModel();
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
     * @return array|string
     */
    public static function statusList($key = -1)
    {
        $aRet = [
            1 => '显示',
            0 => '隐藏',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : 'error';
        }
        return $aRet;
    }

    /**
     * 类型配置
     * @param $key int
     * @return array|string
     */
    public static function typeList($key = -1)
    {
        $aRet = [
            1 => '可访问',
            2 => '不可访问',
            0 => '只作为菜单',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : 'error';
        }
        return $aRet;
    }

}