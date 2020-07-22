<?php
/**
 * 客服管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MaterialService;
use app\admin\service\file\UploadService;
use app\admin\service\LogsService;
use app\admin\service\RbacRoleService;
use app\admin\service\RoleUserService;
use app\admin\service\UserActionRecordService;
use app\admin\service\UserMemberService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;

class StaffManagingController extends AdminBaseController
{
    /**
     * 会员列表
     * @author zjy
     * @throws
     */
    public function index()
    {
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
            'keywords' => !isset($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'user_status' => !isset($params['data']['user_status']) ? '' : $params['data']['user_status'],
            'daren_status' => !isset($params['data']['daren_status']) ? '' : $params['data']['daren_status'],
            'is_vip' => !isset($params['data']['is_vip']) ? '' : $params['data']['is_vip'],
            'is_online' => !isset($params['data']['is_online']) ? '' : $params['data']['is_online'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => empty($params['sortField']) ? 1 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            'kf' => 1,
        ];
        $result = UserMemberService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $item)
            {
                //客服角色

                $opera = '<button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                          </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-success btn-outline btn-sm" onclick="sourcePopup('.$item['id'].')">
                                    <i class="icon wb-user" aria-hidden="true" ></i> 详情
                          </button>';


                switch ($item['user_status']){
                    case 0:
                        $status = '<button type="button" onclick="updStatus('.$item['id'].',1)" class="btn btn-squared btn-outline btn-sm btn-danger">禁用</button>';
                        break;
                    case 1:
                        $status = '<button type="button" onclick="updStatus('.$item['id'].',0)" class="btn btn-squared btn-outline btn-sm btn-primary">正常</button>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }
                $sex = ['保密','男','女'];
                $avatar = MaterialService::getFullUrl($item['avatar']);
                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $item['id'],
                    'sex' => $sex[$item['sex']],
                    'age' => empty($item['age']) ? '无' : $item['age'],
                    'coin' => $item['coin'],
                    'withdraw_coin' => $item['withdraw_coin'],
                    'frozen_coin' => $item['frozen_coin'],
                    'withdraw_frozen_coin' => $item['withdraw_frozen_coin'],
                    'user_nickname' => $item['user_nickname'],
                    'avatar' => '<img src="'.$avatar.'" alt="." width="50px" height="50px"/>',
                    'signature' => $item['signature'],
                    'mobile' => $item['mobile'],
                    'last_login_ip' => $item['last_login_ip'],
                    'qq' => $item['qq'],
                    'y_level' => $item['y_level'],
                    'device_brand' => $item['device_brand'],
                    'weixin' => $item['weixin'],
                    'address' => $item['address'],
                    'speech_introduction' => $item['speech_introduction'],
                    'tags' => $item['tags'],
                    'from_uid' => $item['from_uid'],
                    'device_type' => $item['device_type'],
                    'is_online' => !empty($item['last_online_time']) && $item['last_online_time'] >= time() - 600 ?  '<span style="color:#3e8ef7;">在线</span>' : '<span style="color:#c6d3d7;">离线</span>' ,
                    'status' => $status,
                    'is_vip' => time() < $item['vip_expire_time'] ? '是' : '否',
                    'vip_expire_time' => empty($item['vip_expire_time']) ? '非VIP' : date("Y-m-d",$item['vip_expire_time']),
                    'daren_status' => UserMemberService::statusList($item['daren_status']),
                    'be_follow_num' =>$item['be_follow_num'],
                    'be_look_num' =>$item['be_look_num'],
                    'info_complete' => $item['info_complete'] == 1 ? '已完善' : '未完善',
                    "create_time"=> date("Y-m-d H:i",$item['create_time']),
                    "last_login_time"=> date("Y-m-d H:i",$item['last_login_time']),
                    'opera' => $opera
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
     * 查看详情
     * @throws
     */
    public function Details(){
        $id = $this->request->param('id');
        $info = UserMemberService::ToInfo(['id'=>$id],'*');
        $album = [];
        $video = [];
        if(!empty($info)){
           // array_push(json_decode($info['album'],true),$album);
            if(!empty($info['album'])){
                $album = json_decode(html_entity_decode($info['album']));

                if(count($album) > 0){
                    foreach ($album as $key=>$item){
                        $album[$key] = MaterialService::getFullUrl($item);
                    }
                }
            }
            if(!empty($info['video'])){
                $video = json_decode(html_entity_decode($info['video']));
                if(count($video) > 0){
                    foreach ($video as $k=>$value){
                        $video[$k] = MaterialService::getFullUrl($value);
                    }
                }
            }

        }
        //收到礼物
        $gift['num'] = Db::name('gift_given_order')->where(['receive_uid' => $id])->sum('num');
        $gift['coin'] = Db::name('gift_given_order')->where(['receive_uid' => $id])->sum('coin');
        //赚取金币
        $gold_coin = Db::name('user_coin_record')->where("user_id = {$id} and change_type = 1 and class_id = 4")->sum('change_coin');

        $info['avatar'] = MaterialService::getFullUrl($info['avatar']);

        //var_dump($video);die;

        $sex_type = ['保密', '男', '女'];
        $info['sex_type'] = isset($sex_type[$info['sex']]) ? $sex_type[$info['sex']] : '';
        $this->assign('gift',$gift);
        $this->assign('gold_coin',$gold_coin);
        $this->assign('user_info',$info);
        $this->assign('album',$album);
        $this->assign('video',$video);
        return $this->fetch();
    }

    /**
     * 编辑
     * @throws
     */
    public function edit(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            $condition = [];
            $info = UserMemberService::ToInfo(['id'=>$param['id']],'*');
            if(!isset($param['id']) || !isset($param['user_nickname']) || !isset($param['coin']) || !isset($param['avatar']) || !isset($param['user_status']) || !isset($param['vip_expire_time']) || !isset($param['daren_status'])){
                return json_encode(['code'=>0, 'msg' => '参数错误!']);
            }
            if(strlen($param['user_nickname']) > 50){
                return json_encode(['code'=>0, 'msg' => '昵称过长!']);
            }
            if(!is_numeric($param['coin']) || $param['coin'] < 0){
                return json_encode(['code'=>0, 'msg' => '金币必须为整数!']);
            }
            if(!empty($param['user_nickname'])){
                $condition['user_nickname'] = $param['user_nickname'];
            }
            if(!empty($param['coin'])){
                $condition['coin'] = intval($param['coin']);
                LogsService::addRecord(4,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"修改用户id为{$param['id']}的金币",json_encode($param));
            }

            if(!empty($param['vip_expire_time']) && strtotime($param['vip_expire_time']) && strtotime($param['vip_expire_time']) != $info['vip_expire_time']){
                $condition['vip_expire_time'] = strtotime($param['vip_expire_time']) + 86399;
            }

            if(in_array($param['user_status'],[0,1]) && $param['user_status'] != $info['user_status']){
                $condition['user_status'] = intval($param['user_status']);
            }

            if(in_array($param['daren_status'],[0,1,2,10]) && $param['daren_status'] != $info['daren_status']){
                $condition['daren_status'] = intval($param['daren_status']);
            }
            if(in_array($param['info_complete'],[0,1]) && $param['info_complete'] != $info['info_complete']){
                $condition['info_complete'] = intval($param['info_complete']);
            }
            if(count($condition) < 1){
                return json_encode(['code'=>200, 'msg' => '修改成功,但数据没有变动']);
            }
            $url = MaterialService::getFullUrl($info['avatar']);
            if(!empty($param['avatar']) && substr($param['avatar'],0,4) != 'http' && $url!= $param['avatar']){
                $condition['avatar'] = $param['avatar'];
            }
            $data['uid']= $param['id'];
            foreach ($condition as $key => $item){
                if(isset($info[$key]) && $info[$key] != $item){
                    $data[$key] = ['old'=>$info[$key],'new'=>$item];
                }
            }

            if(count($data) > 1){
                $filter = [
                    'record' => json_encode($data),
                    'create_id' => Session::get('ADMIN_ID'),
                    'create_time' => time(),
                    'create_ip' => $this->request->ip(),
                    'remark' => '会员信息编辑',
                ];
                $record = UserActionRecordService::AddInfo($filter);
                if(!empty($param['coin'])){
                    $condition['coin'] = intval($param['coin']);
                    LogsService::addRecord(4,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"修改用户id{$param['id']}的金币值为{$param['coin']}",json_encode($param));
                }
                LogsService::addRecord(4,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"修改用户id为{$param['id']}的信息",json_encode($param));

                if(!$record){
                    return json_encode(['code'=>0, 'msg'=> '添加记录失败']);
                }
            }

            $res = UserMemberService::UpdateInfo(['id'=>$param['id']],$condition);
            if($res){
                if ($condition['user_status'] == 0) {
                    Db::name('user_token')->where('user_id', $param['id'])->update(['expire_time' => 0]);
                }

                return json_encode(['code'=>200, 'msg'=> '更新成功']);
            }else{
                return json_encode(['code'=>200, 'msg'=> '更新失败']);
            }
        }
        $id = $this->request->param('id');
        $info = UserMemberService::ToInfo(['id'=>$id],'*');
        $info['avatar'] = MaterialService::getFullUrl($info['avatar']);
        //var_dump(date("Y-m-d H:i:s",$info['vip_expire_time'])) ;
        $info['vip_expire_time'] = empty($info['vip_expire_time']) ? '' : date("Y-m-d",$info['vip_expire_time']);

        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 图片上传到阿里云oss
     * @return false|string
     */
    public function uploadFile(){
        $param = $this->request->file();
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $code = '', $lc = strlen($chars)-1; $i < 5; $i++) {
            $code .= $chars[mt_rand(0, $lc)];
        }
        $new_name = time().$code.'.jpg';

        if(isset($param['avatar'])){
            $file = $this->request->file('avatar');
            $tmp_file = $file->getInfo('tmp_name');
            $avatar = 'zhibo/'.date("Y",time()).'/'.date("m",time()).'/'.date('d',time()).'/'.$new_name;
            $res = UploadService::uploadObject(fopen($tmp_file, 'rb'), $avatar);
            //var_dump($res);die;
            if($res != false){
                $data = [
                    'type' => 1,
                    'save_name' => $new_name,
                    'save_dir' => $avatar,
                ];
                return json_encode(['code'=>1, 'data'=>$data]);
            }else{
                return  json_encode(['code'=>0, 'msg'=>'上传失败']);
            }
        }else{
            return  json_encode(['code'=>0, 'msg'=>'网络异常']);
        }

    }

    /**
     * 删除用户相册和视频
     * @throws
     */
    public function AlbumEdit(){
        $param = $this->request->param();
        $txyunOption = cmf_get_option('trtc');
        switch ($param['type']){
            case 1:
                //查询相册数据
                $album_info = UserService::ToInfo(["id"=>$param['id']],'album',-1);
                if(empty($album_info)){
                    return json_encode(['msg'=>'用户数据有误','code'=>0]);
                }
                //转化成数组
                $album_array = json_decode(htmlspecialchars_decode($album_info));
                //匹配当前视频url 截掉域名部分
                if (preg_match('/^http/', $param['url'])) {
                    $url = substr($param['url'],strlen($txyunOption['cosCdn']));
                }else{
                    return json_encode(['msg'=>'资源地址有误','code'=>0]);
                }
                //获取key
                $album_array_key = array_search($url,$album_array);
                //删除当前照片
                unset($album_array[$album_array_key]);
                //重组数组、json、转义
                $album_str = htmlspecialchars(json_encode(array_values($album_array)));
                //修改用户数据
                $res = UserService::UpdateData(['id'=>$param['id']],['album'=>$album_str]);
                LogsService::addRecord(4,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"删除用户id为{$param['id']}的个人相册",json_encode($param));
                if($res){
                    return json_encode(['msg'=>'删除成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'删除失败','code'=>0]);
                }
                break;
            case 2:
                //查询视频数据
                $video_info = UserService::ToInfo(["id"=>$param['id']],'video',-1);
                if(empty($video_info)){
                    return json_encode(['msg'=>'用户数据有误','code'=>0]);
                }
                //转化成数组
                $video_array = json_decode(htmlspecialchars_decode($video_info));
                //匹配当前视频url 截掉域名部分
                if (preg_match('/^http/', $param['url'])) {
                    $url = substr($param['url'],strlen($txyunOption['cosCdn']));
                }else{
                    return json_encode(['msg'=>'资源地址有误','code'=>0]);
                }
                //获取key
                $video_array_key = array_search($url,$video_array);
                //删除当前视频url
                unset($video_array[$video_array_key]);
                //重组数组、json、转义
                $video_str = htmlspecialchars(json_encode(array_values($video_array)));
                //修改用户数据
                $res = UserService::UpdateData(['id'=>$param['id']],['video'=>$video_str]);
                LogsService::addRecord(4,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"删除用户id为{$param['id']}的个人视频",$param);
                if($res){
                    return json_encode(['msg'=>'删除成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'删除失败','code'=>0]);
                }
                break;
            default:
                return json_encode(['msg'=>'无效操作','code'=>0]);
                break;
        }

    }


    /**
     * 添加运营客服
     */
    public function addInfo(){
        return $this->fetch('add');
    }

    /**
     * 搜索用户信息
     * @throws
     */
    public function searchUser(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(!empty($param['keywords'])){
                if(is_numeric($param['keywords'])){
                    $user_info = UserMemberService::ToInfo("id = {$param['keywords']} or mobile = {$param['keywords']}",'user_nickname,id,avatar,mobile');
                }else{
                    $user_info = UserMemberService::ToInfo("user_nickname = '{$param['keywords']}'",'user_nickname,id,avatar,mobile');
                }
                if(empty($user_info)){
                    return json_encode(['code'=>0, 'msg'=>'该用户不存在']);
                }else{
                    $uid_code = "company_promotion_anchor"; //该值为预定义,暂时无法修改
                    $role_id = RbacRoleService::ToInfo(['uni_code'=> $uid_code],'id',-1);
                    $condition = [
                        'user_id' => $user_info['id'],
                        'role_id' => $role_id,
                    ];
                    $is_exist = RoleUserService::ToInfo($condition,'id');
                    if(!empty($is_exist)){
                        $user_info['is'] = 1;
                    }else{
                        $user_info['is'] = 0;
                    }
                    $user_info['avatar_all'] = MaterialService::getFullUrl($user_info['avatar']);
                    return json_encode(['code'=>200, 'data'=>$user_info]);
                }
            }else{
                return json_encode(['code'=>0, 'msg'=>'参数错误']);
            }
        }

    }
}
