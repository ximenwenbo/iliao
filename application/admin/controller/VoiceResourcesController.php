<?php
/**
 * 语音资源管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use app\admin\service\ResourcesService;
use app\admin\service\txyun\YuntongxinService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;

class VoiceResourcesController extends AdminBaseController
{
    /**
     * 视频列表
     * @author zjy
     * @throws
     */
    public function Index()
    {
        $status = ResourcesService::statusListSelect(-1);
        $this->assign('status',$status);
        return $this->fetch();
    }

    /**
     * 列表ajax
     * @throws
     */
    public function ListAjax()
    {
        $params = $this->request->param();
        $condition = [
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'status' => !isset($params['data']['status']) ? 1 : $params['data']['status'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'class_id' => 3,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => !empty($params['sortField']) ? $params['sortField'] : 0,
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = ResourcesService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                //操作按钮
                switch ($val['status'])
                {
                    case 1://审核中
                        $opera = '<button type="button" class="btn btn-success btn-sm btn-outline btn-default" onclick="AuthPopup('.$val['id'].')">
                                    <i class="icon icon wb-user" aria-hidden="true" ></i> 审核
                                 </button>';
                        break;
                    case 10://审核未通过
                        $opera = '<button type="button" class="btn social-google-plus btn-sm " id="authDel">
                                    <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                 </button>';
                        break;
                    default:
                        $opera='';
                        break;
                }
                //转化为阿里云绝对路径
                $oss_url = MaterialService::getFullUrl($val['object']);

                //音频
                $speech_introduction = MaterialService::getFullUrl($val['object']);
                $speech = '
                                <div class="cover plyr" style="overflow: visible;height: 50px">
                                    <audio controls class="playBtn">
                                        <source type="audio/mp3" src="'.$speech_introduction.'">
                                        <source type="audio/ogg" src="'.$speech_introduction.'">
                                        <a href="'.$speech_introduction.'">下载</a>
                                    </audio>
                                </div>
                           ';

                $sex = ['保密','男','女'];
                $filed =  [
                    'id' => $val['id'],
                    'uid' => $val['uid'],
                    'user_nickname' => $val['user_nickname'],
                    'bucket' => $val['bucket'],
                    'like_num' => $val['like_num'],
                    'look_num' => $val['look_num'],
                    'size' => sprintf("%.2f",$val['size']/1024/1024),
                    'class_id' => '个人语音',
                    'object' => $speech,
                    'status' => ResourcesService::statusListSelect($val['status']),
                    "time"=> date("Y-m-d H:i:s",$val['create_time']),
                    "audit_time"=> !empty($val['audit_time']) ? date("Y-m-d H:i:s",$val['audit_time']) : '无',
                    'opera'=> $opera,
                    'mobile' => !empty($val['mobile']) ? $val['mobile'] : '无',
                    'age' => !empty($val['age']) ? $val['age'] : '无',
                    'sex' => !empty($val['sex']) ? $sex[$val['sex']] : '无',
                    'last_login_time' => !empty($val['last_login_time']) ? $val['last_login_time'] : '无',
                    'last_login_ip' => !empty($val['last_login_ip']) ? $val['last_login_ip'] : '无',
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
            "data"=> $params['data']//表单参数
        ]);
    }



    /**
     * 资源审核
     * @throws
     */
    public function toExamine()
    {
        //post数据接收
        if(Request::instance()->isPost()){
            $params = Request::instance()->post();
            //数据验证
            if(!isset($params['id']) || empty($params['id']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>1]);
            }
            if(!isset($params['remark']))
            {
                return json_encode(['msg'=>"备注不能为空", 'code'=>2]);
            }
            if(!isset($params['status']) || !is_numeric($params['status']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>3]);
            }else{
                if($params['status'] == 10)
                {
                    if(mb_strlen($params['remark']) > 255)
                    {
                        return json_encode(['msg'=>"备注请输入1-255个字符", 'code'=>4]);
                    }
                }
                else
                {
                    if(strlen($params['remark']) > 200)
                    {
                        return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>5]);
                    }
                }
            }
            $admin_id=Session::get('ADMIN_ID');
            if(empty($admin_id))
            {
                return json_encode(['code' => 102 ,'msg'=>'请重新登陆']);
            }
            //更新数据
            $condition = [
                'id' => $params['id'],
                'status' => $params['status'],
                'remark' => $params['remark'],
                'update_time' => time(),
                'auditor' => $admin_id,
                'audit_time' => time(),
            ];
            $result = Db::name('oss_material')->update($condition);
            if(empty($result))
            {
                return json_encode(['msg'=>"操作失败，请稍后再试", 'code'=>1]);
            }
            else
            {
                // 审核完成，发送消息通知
                $authRow = Db::name('oss_material')->field('user_id')->find($params['id']);
                if ($params['status'] == 2) { // 通过
                    YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_VOICE_AUTH_SUCCESS');
                } elseif ($params['status'] == 10) { // 拒绝
                    YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_VOICE_AUTH_FAIL');
                }
                return json_encode(['msg'=>"操作成功", 'code'=>200]);
            }

        }
        $id = $this->request->param('id');
        $info = ResourcesService::ToInfo($id);
        if(empty($info)){
            $this->error("该数据不存在或已过期");
        }
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function authDelete()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在",]) ;
        }
        $condition = [
            'id' => $id,
            'status' => -99,
            'update_time' => time(),
        ];
        $result = Db::name('oss_material')->update($condition);
        if(empty($result))
        {
            return json_encode(["status"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["status"=>200, "msg"=>"删除成功!",]);
        }

    }

}
