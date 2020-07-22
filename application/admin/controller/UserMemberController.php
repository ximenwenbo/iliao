<?php
/**
 * 会员管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\file\UploadService;
use app\admin\service\MaterialService;
use app\admin\service\LogsService;
use app\admin\service\RbacRoleService;
use app\admin\service\RoleUserService;
use app\admin\service\UserActionRecordService;
use app\admin\service\UserBalanceLogService;
use app\admin\service\UserMemberService;
use app\admin\service\UserService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;
use think\Validate;

class UserMemberController extends AdminBaseController
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
        ];
        $result = UserMemberService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $item)
            {
                //客服角色
                $is_kefu = RoleUserService::ToInfo(['user_id'=>$item['id'],'role_id'=>3],'id',1);
                if(!$is_kefu){
                    $opera = '<button type="button" class="btn btn-warning" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-success" onclick="sourcePopup('.$item['id'].')">
                                    <i class="icon wb-user" aria-hidden="true" ></i> 查看
                                 </button>';
                }else{
                    $opera = '<button type="button" class="btn btn-warning" onclick="editPopup('.$item['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-success" onclick="sourcePopup('.$item['id'].')">
                                    <i class="icon wb-user" aria-hidden="true" ></i> 查看
                                 </button>';
                }


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

                //性别、头像
                $sex = ['保密','男','女'];
                $avatar = MaterialService::getFullUrl($item['avatar']);
                $avatar_img = '<div class="gallery-wrapper">
                                    <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$avatar."'".')">
                                        <img src="'.$avatar.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                    </a>
                                </div>';

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
                    'avatar' => $avatar_img,
                    'signature' => $item['signature'],
                    'mobile' => $item['mobile'],
                    'last_login_ip' => empty($item['last_login_ip']) ? "空" :$item['last_login_ip'],
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
                    "last_login_time"=> empty($item['last_login_time']) ? "空" : date("Y-m-d H:i",$item['last_login_time']),
                    'opera' => '<a href="javascript:void(0)" style="text-decoration: none" class="more-operate">更多+</a>'
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
     * 转为客服
     * @throws
     */
    public function TurnToCustomer(){
        $id = $this->request->param('id');
        if(!empty($id)){
            //取角色id
            $uid_code = "company_promotion_anchor"; //该值为预定义,暂时无法修改
            $role_id = RbacRoleService::ToInfo(['uni_code'=> $uid_code],'id',-1);
            if(empty($role_id)){
                return json_encode(['code'=>0 ,'msg'=>'暂无客服角色,请先添加客服角色']);
            }
            $condition = [
              'user_id' => $id,
              'role_id' => $role_id,
            ];
            $is_exist = RoleUserService::ToInfo($condition,'id');
            if(!empty($is_exist)){
                return json_encode(['code'=>0 ,'msg'=>'操作无效: 该用户已是客服']);
            }
            $res = RoleUserService::AddInfo($condition);
            if($res){
                return json_encode(['code'=>200, 'msg'=> '操作成功']);
            }else{
                return json_encode(['code'=>0 ,'msg'=>'操作失败']);
            }
        }else{
            return json_encode(['code'=>0,'msg'=>'程序错误']);
        }


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
            //parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                'user_nickname' => 'require|max:50',
                'mobile' => 'number|max:11',
                'age' => 'integer|between:18,80',
                'user_email' => 'email',
                'signature' => 'max:255',
                'coin' => 'number',
                'qq' => 'number|max:100',
                'weixin' => 'max:100',
                'y_level' => 'number',
                'be_follow_num' => 'number',
                'be_look_num' => 'number',
                'watch_num' => 'number',
                'longitude' => 'max:10',
                'latitude' => 'max:10',
            ]);

            $validate->message([
                'user_nickname.require'=> '用户昵称不能为空',
                'user_nickname.max'=> '用户昵称不能超过50个字符',
                'mobile.number'=> '手机号必须为数字',
                'mobile.max'=> '非手机号',
                'age.integer'=> '年龄必须为正整数',
                'age.between'=> '年龄必须在18到80岁之间',
                'user_email.email'=> '邮箱格式不正确',
            ]);

            if (!$validate->check($param)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            //原有数据
            $info = UserMemberService::ToInfo(['id'=>$param['id']],'*');
            if(empty($info)){
                return json_encode(['msg'=>'数据有误,请刷新后重试', 'code'=>0]);
            }

            $data = [];
            $condition = [];
            foreach ($param as $k => $v){
                if(isset($info[$k]) && $v != $info[$k]){ //排除未修改的字段
                    $condition[$k] = $v;
                    $data['old'][$k] = $info[$k]; //记录原始数据 并保存为$data['old']
                }
                if(!empty($param['avatar_dir']) && $info['avatar'] != $param['avatar_dir']){
                    $condition['avatar'] = $param['avatar_dir'];
                    $data['old']['avatar'] = $info['avatar'];
                }
            }
            //保证condition不为空的情况 再进行修改操作 并写入记录
            if(empty($condition)){
                return json_encode(['code'=>200, 'msg' => '修改成功,但数据没有变动']);
            }else{//写入管理员操作记录
                $data['new'] = $condition;
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
            //转化时间为int类型
            if(!empty($condition['vip_expire_time'])){
                $condition['vip_expire_time'] = strtotime($condition['vip_expire_time']);
            }
            //修改数据
            $res = UserMemberService::UpdateInfo(['id'=>$param['id']],$condition);
            if($res){
               /* if ($condition['user_status'] == 0) {
                    Db::name('user_token')->where('user_id', $param['id'])->update(['expire_time' => 0]);
                }*/
                return json_encode(['code'=>200, 'msg'=> '更新成功']);
            }else{
                return json_encode(['code'=>200, 'msg'=> '更新失败']);
            }
        }
        $id = $this->request->param('id');
        $info = UserMemberService::ToInfo(['id'=>$id],'*');
        $album = [];
        $video = [];
        if(empty($info)){
            $this->error('数据出错');
        }
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
        $info['avatar'] = MaterialService::getFullUrl($info['avatar']);
        $info['vip_expire_time'] = empty($info['vip_expire_time']) ? '' : date("Y-m-d H:i",$info['vip_expire_time']);

        $this->assign('album',$album);
        $this->assign('video',$video);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 图片上传
     * @return false|string
     */
    public function uploadFile(){
        $param = $this->request->file();
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $code = '', $lc = strlen($chars)-1; $i < 5; $i++) {
            $code .= $chars[mt_rand(0, $lc)];
        }
        $new_name = md5(microtime() . $code) . '.jpg';

        if(isset($param['avatar'])){
            $file = $this->request->file('avatar');
            $tmp_file = $file->getInfo('tmp_name');
            $avatar = 'upload/'.date("Ymd").'/'.$new_name;
            $res = UploadService::uploadObject(file_get_contents($tmp_file),$avatar);
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
     * 账户管理
     * @throws
     */
    public function AccountManagement(){

        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                'change' => 'require|number|integer|max:10',
                'description' => 'require|max:255',
                'id' => 'require',
            ]);
            $validate->message([
                'change.require' => '变更金币不能为空',
                'change.number' => '变更金币必须是数字',
                'change.max' => '变更金币最高为10位数',
                'change.integer' => '变更金币必须是整数',
                'description.require' => '变更描述不能为空',
                'description.max' => '变更描述最多255的字符',
                'id.require' => '参数有误',
            ]);
            if(! $validate->check($params)){
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            try
            {
                Db::startTrans();
                //记录变添加数据
                $coin = UserMemberService::ToInfo(['id' => $params['id']],'coin',-1);
                $balance = $coin + intval($params['change']);
                $condition = [
                    'user_id' => $params['id'],
                    'changer_uid' => Session::get('ADMIN_ID'),
                    'change' => $params['change'],
                    'balance' => $balance,
                    'description' => $params['description'],
                    'create_time' => time(),
                    'changer_ip' => $this->request->ip(),
                ];
                $res = UserBalanceLogService::AddData($condition);
                if(!$res){
                    return json_encode(['code' => 0, 'msg'=> '金币变更失败']);
                }
                $balance = $balance > 0 ? $balance : 0 ;
                if($balance != $coin){
                    $res = UserMemberService::UpdateInfo(['id' => $params['id']],['coin' => $balance]);
                    if(! $res){
                        return json_encode(['code' => 0, 'msg'=> '金币变更失败']);
                    }
                }
                //变更用户表金币
                Db::commit();

                return json_encode(['code' => 200, 'msg' => '金币变更成功']);
            }catch (Exception $exception){
                Db::rollback();
                return json_encode(['code' => 0, 'msg'=> '操作异常：'.$exception->getMessage()]);
            }

        }
        $id = $this->request->param('id',0);
        //获取用户昵称id金币余额
        $info = UserMemberService::ToInfo(['id' => $id],'user_nickname,id,coin');
        $this->assign('info',$info);
        return $this->fetch('account');
    }

    /**
     * 修改用户状态 禁用 正常
     * @throws
     */
    public function updateUserStatus()
    {
        if($this->request->isAjax()){
            $param = $this->request->param();

            $validate = new Validate([
                'id' => 'require',
                'user_status' => 'require|in:0,1',
            ]);

            $validate->message([
                'user_status.require'=> '状态不能为空',
            ]);

            if (!$validate->check($param)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            //原有数据
            $info = UserMemberService::ToInfo(['id'=>$param['id']],'*');
            if(empty($info)){
                return json_encode(['msg'=>'数据有误,请刷新后重试', 'code'=>0]);
            }

            //修改数据
            $condition = [
                'user_status' => $param['user_status']
            ];
            $res = UserMemberService::UpdateInfo(['id'=>$param['id']],$condition);
            if($res){
                if ($condition['user_status'] == 0) {
                    Db::name('user_token')->where('user_id', $param['id'])->update(['expire_time' => 0]);
                }
                return json_encode(['code'=>200, 'msg'=> '操作成功']);
            }else{
                return json_encode(['code'=>200, 'msg'=> '操作失败']);
            }
        }
    }
}
