<?php
namespace app\admin\service;

use app\admin\model\RealNameAuthModel;
use think\Exception;

class RealNameAuthService extends BaseService
{
    /**
     * 用户反馈列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function AuthList($filter)
    {
        //条件处理
        $where = '1=1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (u.id = '{$filter['keywords']}' or u.user_nickname like '%{$filter['keywords']}%') ";
        }

        //状态
        if (is_numeric($filter['status']))
        {
            $where .= " and a.status = {$filter['status']}";
        }
        else //默认显示认证中
        {
            $where .= " and a.status >= 0";
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
        if(!empty($filter['sortField']) && !empty($filter['sortType']))
        {
            $sort = "a.{$filter['sortField']} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //返回字段
        $field = 'a.*,u.user_nickname, u.mobile, u.age, u.sex, u.last_login_time, u.last_login_ip';

        //join表
        $join = ['a', 'user u', 'u.id = a.user_id'];

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new RealNameAuthModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 状态配置
     * @param $key int
     * @return array
     */
    public static function statusListSelect($key)
    {
        $aRet = [
//            0 => '未认证',
            1 => '待审批',
            2 => '审批通过',
            10 => '审批失败',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }
}