<?php
/**
 * 用户中心管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\LogsService;
use app\admin\service\UserCenterService;
use app\admin\service\UserMessageService;
use cmf\controller\AdminBaseController;
use think\Request;
use think\Session;


class UserCenterController extends AdminBaseController
{
    /**
     * 密码验证
     * @throws
     */
    public function checkPassword(){
        $param = $this->request->param();
        $admin_id = Session::get('ADMIN_ID');
        $user_pass = UserCenterService::ToInfo($admin_id,'user_pass',1);
        if (cmf_compare_password($param['oldPassword'], $user_pass)) {
            return json_encode(['success'=>1,'data'=>$user_pass]);
        }
        return json_encode(['success'=>0,'data'=>cmf_password($param['oldPassword'])]);
    }

    /**
     * 修改密码
     * @throws
     */
    public function changePassword(){
        $param = $this->request->param();
        if($param['newPassword'] == $param['oldPassword']){
            return json_encode(['success'=>0,'msg'=> '新密码不能与旧密码一致']);
        }
        if($param['newPassword'] != $param['repPassword']){
            return json_encode(['success'=>0,'msg'=> '两次密码不一致']);
        }
        $admin_id = Session::get('ADMIN_ID');
        $res = UserCenterService::UpdateB(['id'=> $admin_id],['user_pass'=>cmf_password($param['newPassword'])]);
        $request = Request::instance();
        if($res){
            LogsService::addRecord(1,'/'.$request->controller().'/'.$request->action(),'密码修改成功','');
            return json_encode(['success'=>1,'msg'=>'修改成功']);
        }else{
            LogsService::addRecord(1,'/'.$request->controller().'/'.$request->action(),'密码修改失败','');
            return json_encode(['success'=>1, 'msg'=>'修改失败']);
        }
    }

    /**
     * 消息列表
     * @return false|string
     */
    public function messageAjax()
    {
        $params = $this->request->param();
        $condition = [
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => 'id',
            'sortType' => 'desc',
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = UserCenterService::messageList($condition);
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                //定义操作类型
                $filed =  [
                    'id' => $val['id'],
                    'type' => $val['type'],
                    'user_nickname' => $val['user_nickname'],
                    'title' => $val['title'],
                    'content' => $val['content'],
                    "sendTime"=> date("Y-m-d H:i:s",$val['create_time']),
                    "readFlag"=> $val['read_flag'],
                ];
                array_push($data,$filed);
            }
        }
        return json_encode([
            "pageIndex"=> $params['pageIndex'],//分页索引
            "pageSize"=> $params['pageSize'],//每页显示数量
            "totalPage"=> count($data),//分页记录
            "sortField"=> 'id',//排序字段
            "sortType"=> 'desc',//排序类型
            "total"=> $result['total'],//总记录数
            'pageList'=>$data,//分页数据
            "data"=> $params//表单参数
        ]);
    }

    /**
     * 已读
     * @throws
     */
    public function alreadyRead(){
        $id = $this->request->param('id');
        $type = $this->request->param('type');
        if($type==1){
            $res = UserMessageService::UpdateB(['id'=>$id],['status' => -99]);
        }else{
            $res = UserMessageService::UpdateB(['id'=>$id],['read_flag' => 1]);
        }
        if($res == 1){
            return json_encode(['code'=>200, 'msg'=>'标记成功']);
        }else{
            return json_encode(['code'=>0, 'msg'=>'标记失败']);
        }
    }
}
