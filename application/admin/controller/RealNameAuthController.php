<?php
/**
 * 认证管理
 */
namespace app\admin\controller;


use app\admin\service\MaterialService;
use app\admin\service\RealNameAuthService;
use app\admin\service\txyun\YuntongxinService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class RealNameAuthController extends AdminBaseController
{
    /**
     * 实名认证列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $status = RealNameAuthService::statusListSelect(-1);
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
            $result = RealNameAuthService::AuthList($condition);
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
                                    <i class="icon wb-user" aria-hidden="true" ></i> 审核
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
                    //点击图片显示原图
                    $idcard_front = MaterialService::getFullUrl($item['idcard_front']);
                    $idcard_back= MaterialService::getFullUrl($item['idcard_back']);
                    $front = '<div class="gallery-wrapper">
                                    <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$idcard_front."'".')">
                                        <img src="'.$idcard_front.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                    </a>
                                </div>';
                    $back = '<div class="gallery-wrapper">
                                    <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$idcard_back."'".')">
                                        <img src="'.$idcard_back.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                    </a>
                                </div>';
                    $sex = ['保密','男','女'];
                    //database显示字段
                    $filed = [
//                        'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                        'id' => $item['id'],
                        'uid' => $item['user_id'],
                        'nickname' => $item['user_nickname'],
                        'mobile' => $item['mobile'],
                        'sex' => $sex[$item['sex']],
                        'age' => $item['age'],
                        'last_login_time' => empty($item['last_login_time'])? '无' : date("Y-m-d H:i",$item['last_login_time']),
                        'last_login_ip' => $item['last_login_ip'],
                        'front' => $front,
                        'back' => $back,
                        'real_name' => $item['real_name'],
                        'idcard_no' => $item['idcard_no'],
                        'time' => date("Y-m-d H:i",$item['create_time']),
                        'audit_time' => !empty($item['audit_time']) ? date("Y-m-d H:i",$item['audit_time']) : '未审核',
                        'status' => RealNameAuthService::statusListSelect($item['status']),
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
     * 实名认证-审核业务
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
            if(!isset($params['status']) || !is_numeric($params['status']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>3]);
            }else{
                if($params['status'] == 10)
                {
                    if(empty($params['error_msg']) || mb_strlen($params['error_msg']) > 255)
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
            $admin_id=Session::get('ADMIN_ID');
            if(empty($admin_id))
            {
                return json_encode(['code' => 102 ,'msg'=>'请重新登陆']);
            }
            //更新数据
            $condition = [
                'id' => $params['id'],
                'status' => $params['status'],
                'error_msg' => $params['error_msg'],
                'update_time' => time(),
                'auditor' => $admin_id,
                'audit_time' => time(),
            ];
            $result = Db::name('user_auth')->update($condition);
            if(empty($result))
            {
                return json_encode(['msg'=>"操作失败，请稍后再试", 'code'=>1]);
            }
            else
            {
                // 审核完成，发送消息通知
                $authRow = Db::name('user_auth')->field('user_id')->find($params['id']);
                if ($params['status'] == 2) { // 通过
                    $res = YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_AUTH_SUCCESS');
                    if(!$res){
                        return json_encode(['msg'=>"发送通知信息失败", 'code'=>10]);
                    }
                } elseif ($params['status'] == 10) { // 拒绝
                    $res = YuntongxinService::pushSysNotice($authRow['user_id'], 'SYS_AUTH_FAIL');
                    if(!$res){
                        return json_encode(['msg'=>"发送通知信息失败", 'code'=>20]);
                    }
                }

                return json_encode(['msg'=>"操作成功", 'code'=>200]);
            }

        }
        $param = $this->request->param();
        if(!isset($param['id']) || empty($param['id'])){
            return $this->error("参数错误，请稍后再试");
        }
        //判断数据是否存在
        $info = Db::name('user_auth')
                        ->where(['a.id'=>$param['id']])
                        ->alias('a')
                        ->join('user u','u.id=a.user_id')
                        ->field('u.user_nickname, u.mobile, a.idcard_no, a.real_name, a.id')
                        ->find();
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
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
        $result = Db::name('user_auth')->update($condition);
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
