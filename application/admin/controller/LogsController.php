<?php
/**
 * 日志记录管理
 * User: ZJY
 * Date: 2018/12/19
 * Time: 14:12
 */

namespace app\admin\controller;


use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;

class LogsController extends AdminBaseController
{
    /**
     * 日志记录列表
     * @throws
     */
    public function AdminLogsIndex()
    {
        return $this->fetch('logs');
    }

    /**
     * 列表ajax
     * @throws Exception
     */
    public function LogsAjax()
    {
        //获取表单参数
        $param = $this->request->param();
        $pageIndex = isset($param['pageIndex'])&&!empty($param['pageIndex'])? $param['pageIndex'] : 0;
        $sortField = isset($param['sortField'])&&!empty($param['sortField'])? $param['sortField'] : 'id';
        $sortType = isset($param['sortType'])&&!empty($param['sortType'])? $param['sortType'] : 'desc';
        $pageSize = isset($param['pageSize'])&&!empty($param['pageSize'])? $param['pageSize'] : 10;
        //查询数据
        $sql = Db::name('admin_log_record')
            ->alias('log')
            ->field('log.*,u.id as uid, u.user_nickname, u.last_login_time, u.user_login, u.last_login_ip')
            ->join('user u','u.id=log.admin_id')
            ->where(['log.status'=> 1]);
        //添加搜索参数
        if(isset($param['data']) && !empty($param['data'])){

            if(!empty($param['data']['startDate']))
            {
                $create_time = strtotime($param['data']['startDate']);
                $sql->where("log.create_time >= $create_time");
            }
            if(!empty($param['data']['endDate']))
            {
                $create_time = strtotime($param['data']['endDate'])+86399;
                $sql->where("log.create_time <= $create_time");
            }
            if(!empty($param['data']['userId']))
            {
                $sql->where("u.user_login = '{$param['data']['userId']}'");
            }
        }
        //获取记录总数
        $sql_count = clone $sql;
        $total = $sql_count->count();
        //查询数据 排序 分页
        $res = $sql->order("log.{$sortField} {$sortType}")->limit($pageIndex,$pageSize)->select()->toArray();
        $data=[];
        $num = 0;
        if($res)
        {
            //定义操作类型
            $type = ['无','登陆','登出','修改密码','修改会员信息','修改机器人信息'];
            foreach ($res as $val)
            {
                $filed =  [
                    'id' => $val['id'],
                    'user_login' => $val['user_login'],
                    'type' => $type[$val['type']],
                    'url' => $val['url'],
                    "create_time"=> date("Y-m-d H:i:s",$val['create_time']),
                    "admin_ip"=> $val['admin_ip'],
                    "remark"=> $val['remark'],
                ];
                array_push($data,$filed);
                $num++;
            }
        }

        return json_encode([
            "pageIndex"=> $pageIndex,//分页索引
            "pageSize"=> $pageSize,//每页显示数量
            "totalPage"=> $num,//分页记录
            "sortField"=> $sortField,//排序字段
            "sortType"=> $sortType,//排序类型
            "total"=> $total,//总记录数
            'pageList'=>$data,//分页数据
            "data"=> $param['data']//表单参数
        ]);
    }
}