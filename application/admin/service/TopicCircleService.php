<?php
namespace app\admin\service;

use app\admin\model\TopicCircleModel;
use think\Exception;
use think\Log;

class TopicCircleService extends BaseService
{
    /**
     * 社区列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function PostList($filter)
    {
        //条件处理
        $where = '1=1';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (u.nickname like '%{$filter['keywords']}%')  ";
        }
        if (!empty($filter['xchat_y_level']))
        {
            $where .= " and a.xchat_y_level = {$filter['xchat_y_level']}";
        }

        //状态
        if (is_numeric($filter['cj_status']))
        {
            $where .= " and a.cj_status = {$filter['cj_status']}";
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
        $sort = "a.create_time desc";

        //连表
        $join = ['a','t_jiaoliuqu_account u', 'u.account_uuid=a.account_uuid'];
        //返回字段
        $field = 'a.*, u.account_card_id, u.account_uuid, u.nickname, u.avatar,u.sex_type,u.city';

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 5;

        //limit
        $offset = !empty($filter['current_page']) ? $filter['current_page']*$pageSize : 0;

        //$group = 'a.account_uuid';
        //调用模型 处理
        //return $offset;

        $model = new TopicCircleModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize,$group = '');
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 回复列表
     * @param $where array|string
     * @throws
     * @return array
     */
    public static function ReviewList($where){
        //return $where;
        $data = TopicCircleModel::TableSelect('t_jiaoliuqu_review',$where,'id asc','','');
        if($data){
            return $data;
        }else{
            return [];
        }
    }

    /**
     * 状态配置
     * @param $key int
     * @return array
     */
    public static function statusList($key = -999)
    {
        $aRet = [
            0 => '未采用',
            1 => '已采用',
            5 => '同步成功',
            10 => '同步失败',
            -99 => '不采用',
        ];
        if($key != -999){
            return $aRet[$key];
        }
        return $aRet;
    }

}