<?php
/**
 * 发送用户消息
 * @author zjy
 * Date 2019/05/08
 */
namespace app\admin\controller;

use app\admin\service\SendUserMessagesService;
use app\admin\service\txyun\YuntongxinService;
use cmf\controller\AdminBaseController;


class SendUserMessagesController extends AdminBaseController
{
    /**
     * 消息列表
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'type' => empty($params['data']['type']) ? '' : $params['data']['type'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' =>  !isset($params['sortField']) ? 0 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = SendUserMessagesService::RList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $val)
                {
                    $admin_id = cmf_get_current_admin_id();
                    switch ($val['status']){
                        case 1:
                            $status = '<span style="color:green;">发送成功</span>';
                            break;
                        case 2:
                            $status = '<span style="color:red;">发送失败</span>';
                            break;
                        default:
                            $status = '未知';
                            break;
                    }
                    $content = mb_strlen($val['content']) > 20 ? mb_substr($val['content'],0,20,'utf-8').'...' : $val['content'];

                    //编辑人
                    $filed =  [
                        'id' => $val['id'],
                        'u1' => $val['sender_id'],
                        'u2' => $val['receive_id'],
                        'title' => $val['title'],
                        'content' => '<span title="'.$val['content'].'">'.$content.'</span>',
                        'type' => $val['type'] == 1 ? '后台' : 'App端',
                        "create_time"=> date("Y-m-d H:i",$val['create_time']),
                        'status' => $status,
                    ];
                    array_push($data,$filed);
                }
            }
            return json_encode([
                "pageIndex"=> $params['pageIndex'],//分页索引
                "pageSize"=> $params['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "sortField"=> $condition['sortField'],//排序字段
                "sortType"=> $condition['sortType'],//排序类型
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $params['data']//表单参数
            ]);
        }
        return $this->fetch();
    }


    /**
     * 给用户发送消息
     * @throws
     */
    public function SendMessage(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(empty($param['receive_id']) || empty($param['content']) || empty($param['title']) ){
                return json_encode(['msg' => '必填项不能为空','code' => 0]);
            }
            if(mb_strlen($param['title']) > 50){
                return json_encode(['msg' => '标题过长','code' => 0]);
            }
            if(mb_strlen($param['content']) > 255){
                return json_encode(['msg' => '内容过长','code' => 0]);
            }
            $condition = [
                'sender_id' => cmf_get_current_admin_id(),
                'receive_id' => $param['receive_id'],
                'title' => $param['title'],
                'content' => $param['content'],
                'type' => $param['type'],
                'create_time' => time(),
            ];
            if(!empty($param['remark'])){
                if(mb_strlen($param['remark']) > 255){
                    return json_encode(['msg' => '备注过长','code' => 0]);
                }
                $condition['remark'] = $param['remark'];
            }
            $res = YuntongxinService::pushSysNotice($param['receive_id'], $param['content']);
            if($res){
                $condition['status'] = 1;
            }else{
                $condition['status'] = 2;
            }
            $result = SendUserMessagesService::AddData($condition);
            if($result){
                return json_encode(['msg' => '发送成功','code' => 200]);
            }else{
                return json_encode(['msg' => '发送失败','code' => 0]);
            }
        }
        return $this->fetch('add');
    }

}
