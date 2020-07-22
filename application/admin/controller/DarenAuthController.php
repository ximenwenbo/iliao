<?php
/**
 * 认证管理
 */
namespace app\admin\controller;


use app\admin\service\MaterialService;
use app\admin\service\DarenAuthService;
use app\admin\service\txyun\YuntongxinService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class DarenAuthController extends AdminBaseController
{
    /**
     * 实名认证列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $status = DarenAuthService::statusListSelect(-1);
        $this->assign('status',$status);
        return $this->fetch();
    }

    /**
     * 列表ajax
     * @throws \think\Exception
     */
    public function ListAjax()
    {
        try{
            //参数处理
            $param = $this->request->param();
            $condition = [
                'keywords' => isset($param['data']['keywords']) ? $param['data']['keywords'] : '',
                'status' => isset($param['data']['status']) ? $param['data']['status'] : '',
                'start_time' => isset($param['data']['startDate']) && strtotime($param['data']['startDate']) ? strtotime($param['data']['startDate']) : 0,
                'end_time' => isset($param['data']['endDate']) && strtotime($param['data']['endDate']) ? strtotime($param['data']['endDate'])+86399 : 0,
                'sortField' => 'id',
                'sortType' => isset($param['sortType']) ?  $param['sortType'] : 'desc',
                'offset' => isset($param['pageIndex']) ?  $param['pageIndex'] : 0,
                'pageSize' => isset($param['pageSize']) ?  $param['pageSize'] : 0,
            ];
            //获取表数据
            $result = DarenAuthService::AuthList($condition);
            //var_dump($result);die;
            $data = [];
            if($result)
            {
                foreach ($result['data'] as $item)
                {
                    $opera = '';
                    //操作按钮
                    switch ($item['status'])
                    {
                       case 0://未认证
                           break;
                       case 1://认证中
                           $opera = '<button type="button" class="btn btn-success btn-sm btn-outline btn-default" onclick="AuthPopup('.$item['id'].')">
                                        <i class="icon icon wb-user" aria-hidden="true" ></i> 审核
                                     </button>';
                           break;
                       case 2://认证通过
                           break;
                       case 10://认证失败
                           $opera = '<button type="button" class="btn social-google-plus btn-sm " id="authDel">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                           break;
                       default:
                            $opera='';
                            break;
                    }



                    //音频
                    $speech_introduction = MaterialService::getFullUrl($item['speech_introduction']);
                    $speech = '

                                <div class="cover plyr" style="overflow: visible">
                                    <audio controls class="playBtn">
                                        <source type="audio/mp3" src="'.$speech_introduction.'">
                                        <source type="audio/ogg" src="'.$speech_introduction.'">
                                        <a href="'.$speech_introduction.'">下载</a>
                                    </audio>
                                </div>
                           
';

                    //生活照
                    $life_photo= MaterialService::getFullUrl($item['life_photo']);
                    $life_photo ='<div class="gallery-wrapper">
                                    <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$life_photo."'".')">
                                        <img src="'.$life_photo.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                    </a>
                                </div>';
                    $sex = ['保密','男','女'];
                    //database显示字段
                    $filed = [
                        'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                        'id' => $item['id'],
                        'uid' => $item['user_id'],
                        'nickname' => $item['user_nickname'],
                        'mobile' => $item['mobile'],
                        'sex' => $sex[$item['sex']],
                        'age' => $item['age'],
                        'last_login_time' => empty($item['last_login_time'])? '无' : date("Y-m-d H:i",$item['last_login_time']),
                        'last_login_ip' => $item['last_login_ip'],
                        'life_photo' => $life_photo,
                        'speech_introduction' => $speech,
                        'user_nickname' => $item['user_nickname'],
                        'time' => date("Y-m-d H:i",$item['create_time']),
                        'audit_time' => !empty($item['audit_time']) ? date("Y-m-d H:i",$item['audit_time']) : '未审核',
                        'status' => DarenAuthService::statusListSelect($item['status']),
                        'opera' => $opera
                    ];
                    array_push($data,$filed);
                }
            }
            return json_encode([
                "pageIndex"=> $param['pageIndex'],//分页索引
                "pageSize"=> $param['pageSize'],//每页显示数量
                "totalPage"=> count($data),//分页记录
                "total"=> $result['total'],//总记录数
                'pageList'=>$data,//分页数据
                "data"=> $param['data']//表单参数
            ]);
        }catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 达人认证-审核业务
     * @param array 接收参数
     * @return mixed|void
     * @author zjy
     * @throws
     */
    public function toExamine(){
        //post数据接收
        if(Request::instance()->isPost()){
            $params = Request::instance()->post();
            //数据验证
            if(!isset($params['id']) || empty($params['id']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>1]);
            }
            if(!isset($params['error_msg']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>2]);
            }
            if(!isset($params['status']) || !in_array($params['status'],[2,10]))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>3]);
            }else{
                if($params['status'] == 10)
                {
                    if(mb_strlen($params['error_msg']) > 255)
                    {
                        return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>4]);
                    }
                }
                else
                {
                    if(strlen($params['error_msg']) > 200)
                    {
                        return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>5]);
                    }
                }
            }
            //事务修改2表
            try
            {
                Db::startTrans();
                $admin_id=Session::get('ADMIN_ID');
                if(empty($admin_id))
                {
                    return json_encode(['code' => 102 ,'msg'=>'请重新登陆']);
                }
                //更新认证表数据
                $condition = [
                    'id' => $params['id'],
                    'status' => $params['status'],
                    'error_msg' => $params['error_msg'],
                    'update_time' => time(),
                    'auditor' => $admin_id,
                    'audit_time' => time(),
                ];
                $result = Db::name('user_daren_auth')->update($condition);
                if(empty($result))
                {
                    throw new Exception('操作失败，请稍后再试');
                }

                $user = Db::name('user_daren_auth')->where(['id'=>$params['id']])->field('user_id,speech_introduction,life_photo')->find();
                //认证通过更改user信息
                if($params['status'] == 2)
                {
                    $user_condition = [
                        'id' => $user['user_id'],
                        'daren_status' => $params['status'],
                    ];
                }else{
                    $user_condition = [
                        'id' => $user['user_id'],
                        'daren_status' => $params['status'],
                    ];
                }
                $user_res = Db::name('user')->update($user_condition);
                if(empty($user_res))
                {
                    throw new Exception('操作失败，请稍后再试');
                }

                // 审核完成，发送消息通知
                $authRow = Db::name('user_daren_auth')->field('user_id')->find($params['id']);
                if(empty($authRow)){
                    return json_encode(['msg'=>"数据不存在", 'code'=>10]);
                }

                if ($params['status'] == 2) { // 通过
                    $res = YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_DAREN_AUTH_SUCCESS');
                    if(!$res){
                        return json_encode(['msg'=>"发送通知信息失败", 'code'=>10]);
                    }
                } else{ // 拒绝
                    $res = YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_DAREN_AUTH_FAIL');
                    if(!$res){
                        return json_encode(['msg'=>"发送通知信息失败", 'code'=>20]);
                    }
                }
                Db::commit();
                return json_encode(['msg'=>"操作成功", 'code'=>200]);
            }catch (Exception $e){
                //获取异常信息：
                Db::rollback();
                return json_encode(['msg'=>$e->getMessage(), 'code'=>1]);
            }

        }
        $param = $this->request->param();
        if(!isset($param['id']) || empty($param['id'])){
            $this->error("参数错误，请稍后再试");
        }
        //判断数据是否存在
        $info = Db::name('user_daren_auth')
                        ->where(['a.id'=>$param['id']])
                        ->alias('a')
                        ->join('user u','u.id=a.user_id')
                        ->field('u.user_nickname, u.mobile, a.life_photo, a.id')
                        ->find();
        if(empty($info)){
            $this->error("该数据不存在或已过期");
        }
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 实名认证 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function authDelete()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在",]);
        }
        $condition = [
            'id' => $id,
            'status' => -99,
            'update_time' => time(),
        ];
        $result = Db::name('user_daren_auth')->update($condition);
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
