<?php
namespace app\admin\service;

use app\admin\model\FeedbackModel;
use think\Exception;

class FeedbackService extends BaseService
{
    /**
     * 用户反馈列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws Exception
     * @author zjy
     */
    public function FeedbackList($filter)
    {
        //条件处理
        $where = '1=1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.user_id = '{$filter['keywords']}' or u.user_nickname like '%{$filter['keywords']}%')  ";
        }
        if (isset($filter['status']) && is_numeric($filter['status']))
        {
            $where .= " and a.status = {$filter['status']}";
        }
        else
        {
            $where .= " and a.status > 0";
        }
        if(isset($filter['start_time']) && !empty($filter['start_time'])){
            $where .= " and unix_timestamp(a.create_time) >= {$filter['start_time']}";
        }
        if(isset($filter['end_time']) && !empty($filter['end_time'])){
            $where .= " and unix_timestamp(a.create_time) <= {$filter['end_time']}";
        }

        if(!empty($filter['sortField']) && !empty($filter['sortType']))
        {
            $sort = "a.{$filter['sortField']} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //返回字段
        $field = 'a.*,u.user_nickname, u.mobile';
        //连表
        $join = ['a', 'user u', 'u.id = a.user_id'];
        //调用模型 处理
        $model = new FeedbackModel();
        $result = $model->selectAll($where,$join,$field,$sort,$filter['offset'],$filter['pageSize']);
        return $result;
    }

    /**
     * 状态配置
     * @return array
     */
    public function statusListSelect()
    {
        $aRet = [
            1 => '显示',
            -99 => '已删除',
        ];

        return $aRet;
    }
}