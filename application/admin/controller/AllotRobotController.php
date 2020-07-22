<?php
/**
 * 机器人管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\model\UserModel;
use app\admin\service\MaterialService;
use app\admin\service\file\UploadService;
use app\admin\service\AllotRobotService;
use app\admin\service\LogsService;
use app\admin\service\MerchantCustomerService;
use app\admin\service\MerchantManagementService;
use app\admin\service\ResourcesService;
use app\admin\service\RoleUserService;
use app\admin\service\UserMemberService;
use app\admin\service\UserService;
use app\admin\service\UserSettingService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;
use think\Session;
use think\Validate;


class AllotRobotController extends AdminBaseController
{
    /**
     * 机器人列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        //所有商户
        $management = MerchantManagementService::getManagementInfo();
        $this->assign('management',$management);
        return $this->fetch('index');
    }

    /**
     * 下拉框获取商户下客服
     * @throws
     */
    public function getCustomer()
    {
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(!empty($param['custom_id'])){
                if($param['custom_id'] == -1){//非商户客服
                    $customer_info = MerchantCustomerService::getTableInfo("status=1",  'user_id');
                    $customer_id = implode(',',$customer_info);
                    $query = Db::name('role_user')
                        ->alias('a')
                        ->join('user u','u.id=a.user_id')
                        ->where('a.role_id', 3)
                        ->field('a.user_id,u.user_nickname');
                    if (! empty($customer_id)) {
                        $query->where("a.user_id not in({$customer_id})");
                    }
                    $user_role = $query->select()->toArray();
                }else{//商户下的客服
                    $customer_info = MerchantCustomerService::getTableInfo("status=1 and m_id = {$param['custom_id']}",  'user_id');
                    $customer_id = implode(',',$customer_info);
                    $user_role = Db::name('role_user')
                        ->where("role_id = 3 and user_id in({$customer_id})")
                        ->alias('a')
                        ->join('user u','u.id=a.user_id')
                        ->field('a.user_id,u.user_nickname')
                        ->select()->toArray();
                }
                $customer = empty($user_role) ? [] : $user_role;
                return json_encode(['msg'=>1, 'code'=>200, 'data'=>$customer]);
            }
            return json_encode(['msg'=>1, 'code'=>200, 'data'=>[]]);
        }
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
            'sex' => !isset($params['data']['sex']) ? '' : $params['data']['sex'],
            'daren_status' => !isset($params['data']['daren_status']) ? '' : $params['data']['daren_status'],
            'is_vip' => !isset($params['data']['is_vip']) ? -1 : $params['data']['is_vip'],
            'virtual_pos' => !isset($params['data']['virtual_pos']) ? '' : $params['data']['virtual_pos'],
            'user_id' => empty($params['data']['user_id']) ? '' : $params['data']['user_id'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = AllotRobotService::RList($condition);
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '  <button type="button" class="btn btn-warning btn-outline btn-sm" onclick="editUserPopup('.$val['user_id'].')">
                                <i class="icon wb-edit" aria-hidden="true" ></i> 修改
                            </button>
                            <button type="button" class="btn btn-info btn-sm btn-outline" onclick="UserSettingPopup('.$val['user_id'].')">
                                <i class="icon wb-time" aria-hidden="true" ></i> 收费
                            </button>
                            <button type="button" class="btn btn-success btn-outline btn-sm" onclick="sourcePopup('.$val['user_id'].')">
                                <i class="icon wb-user" aria-hidden="true" ></i> 详情
                            </button>
                            <button type="button" class="btn btn-sm btn-outline btn-danger"  onclick="SettingSourcePopup('.$val['user_id'].')">相册视频</button>
                            ';

                switch ($val['user_status']){
                    case 0:
                        $status = '<button type="button" onclick="updStatus('.$val['user_id'].',1)" class="btn btn-squared btn-outline btn-sm btn-danger">禁用</button>';
                        break;
                    case 1:
                        $status = '<button type="button" onclick="updStatus('.$val['user_id'].',0)" class="btn btn-squared btn-outline btn-sm btn-primary">正常</button>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }

                //头像
                $avatar= MaterialService::getFullUrl($val['avatar']);
                $avatar_img = '<div class="gallery-wrapper">
                                    <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$avatar."'".')">
                                        <img src="'.$avatar.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                    </a>
                                </div>';
                //客服昵称
                $custom_info = UserModel::getUserInfo($val['custom_id'],'user_nickname',-1);
                //客服所属商户
                $management = Db::name('merchant_customer a')->join('merchant_management m','m.id = a.m_id')->field('m.name')->where("user_id = {$val['custom_id']}")->select()->toArray();
                $m_name = isset($management[0]['name']) ? ' ('.$management[0]['name'].')' : '';

                $custom_info = empty($custom_info) ? '无' : $custom_info;
                $sex = ['保密','男','女'];
                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'robot_id' => $val['robot_id'],
                    'nickname' => $val['user_nickname'],
                    'sex' => isset($sex[$val['sex']]) ? $sex[$val['sex']] : '未知',
                    'avatar' => $avatar_img,
                    'status' => $status,
                    'custom_id' => $val['custom_id'],
                    'custom_info' => '<span onclick="editPopup('.$val['id'].')">'.$custom_info.$m_name.'</span>',
                    'create_id' => $val['create_id'],
                    'mobile' => $val['mobile'],
                    'allot_id' =>  empty($val['allot_id']) ? '无' : $val['allot_id'],
                    'is_daren' =>  $val['daren_status'] == 2 ? '是' : '否',
                    'is_vip' =>  $val['vip_expire_time'] > time() ? 'vip' : '否',
                    'virtual_pos' =>  $val['virtual_pos'] == 1 ? '是' : '否',
                    "create_time"=> date("Y-m-d",$val['create_time']),
                    "allot_time"=>  empty($val['allot_time']) ? '无' : date("Y-m-d H:i",$val['allot_time']),
                    "allot_ip"=> empty($val['allot_ip']) ? '无' : $val['allot_id'],
                    'opera' => $opera
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

    /**
     * 添加机器人
     * @throws
     */
    public function AddInfo(){
        //echo 1;die;
        if($this->request->isAjax()){

            $validate = new Validate([
                'avatar' => 'require', //头像
                'user_nickname' => 'require|max:50',//昵称
                'mobile' => [
                    'number',
                    'regex' => '(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$', //验证手机号
                ],
                'sex' => 'require',
                'age' => 'require|number|between:18,80',
                'signature' => 'max:255',
                'address' => 'max:100',
                'province_name' => 'require',
                'city_name' => 'require',
            ]);

            $validate->message([
                'avatar.require' => '头像不能为空',
                'sex.require' => '性别不能为空',
                'age.require' => '年龄不能为空',
                'user_nickname.require' => '请输入用户昵称',
                'user_nickname.max' => '昵称不能超过50个字符',
                'mobile.number' => '手机号必须为数字',
                'mobile.regex' => '手机号格式不正确',
                'upload_image.require' => '头像不能为空',
                'age.number' => '年龄必须为正整数',
                'age.between' => '年龄必须在18到80之间',
                'signature.max' => '签名不能超过255个字符',
                'signature.address' => '地址不能超过100个字符',
                'province_name.require' => '省份不能为空',
                'city_name.require' => '城市不能为空',
            ]);

            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }


            try {
                Db::startTrans();

                $condition = [
                    'user_type' => 3,  //机器人类型
                    'avatar' => $params['avatar'],
                    'user_nickname' => $params['user_nickname'],
                    'mobile' => $params['mobile'],
                    'age' => $params['age'],
                    'signature' => $params['signature'],
                    'province_name' => isset($params['province_name']) ? $params['province_name'] : '',
                    'city_name' => isset($params['city_name']) ? $params['city_name'] : '',
                    'district_name' => isset($params['district_name']) ? $params['district_name'] : '',
                    'address' => isset($params['address']) ? $params['address'] : '',
                    'be_follow_num' => $params['be_follow_num'],
                    'be_look_num' => $params['be_look_num'],
                    'qq' => $params['qq'],
                    'weixin' => $params['weixin'],
                    'sex' => $params['sex'],
                    'virtual_pos' => $params['virtual_pos'],
                    'open_position' => $params['open_position'],
                    'daren_status' => $params['daren_status'],
                    'create_time' => time(),
                    'vip_expire_time' => empty($params['vip_expire_time']) ? 0 : strtotime($params['vip_expire_time']),
                ];

                // 获取该城市的任意一个位置的坐标
                $posRes = $this->getVicinityPos($condition['city_name']);
                if ($posRes == false) {
                    return json_encode(['code'=>0, 'msg' => '获取附近poi失败，请检查错误日志并重新操作']);
                }
                $posid = array_rand($posRes['results'], 1);
                $condition['longitude'] = $posRes['results'][$posid]['location']['lng'];
                $condition['latitude'] = $posRes['results'][$posid]['location']['lat'];

                $userId = UserMemberService::AddInfo($condition);
                if (! $userId) {
                    throw new Exception('机器人创建失败');
                }

                // 完善头像物料
                if (! empty($condition['avatar'])) {
                    Db::name('oss_material')->where('object', $condition['avatar'])->update(['user_id' => $userId]);
                }

                // 添加机器所属客服
                $add_data = [
                    'robot_id' => $userId,
                    'custom_id' => \think\Config::get('option.super_custom_uid'),   //默认客服uid
                    'allot_id' => cmf_get_current_admin_id(), //当前添加人
                    'create_time' => time(), //当前添加时间
                ];

                $allot_robot_id = AllotRobotService::AddData($add_data);
                if(!$allot_robot_id){
                    throw new Exception('机器人分配客服失败');
                }
                Db::commit();
                return json_encode(['code'=>200, 'msg' => '添加成功']);
            } catch (Exception $e) {
                //如获取到异常信息，对所有表的删、改、写操作，都会回滚至操作前的状态：
                Db::rollback();
                $this->error($e);
                return json_encode(['code'=>0, 'msg' => $e->getMessage()]);
            }

        }
        return $this->fetch('add');
    }

    /**
     * 修改归属客服
     * @throws
     */
    public function edit()
    {
        //post数据接收
        if(Request::instance()->isPost()){
            $params = Request::instance()->post();
            //数据验证
            if(!isset($params['id']) || empty($params['id']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>1]);
            }
            if(!isset($params['custom_id']) || empty($params['custom_id']))
            {
                return json_encode(['msg'=>"请选择客服", 'code'=>333]);
            }
            if(!isset($params['remark']))
            {
                return json_encode(['msg'=>"请填写备注", 'code'=>2]);
            }

            if(strlen($params['remark']) > 200)
            {
                return json_encode(['msg'=>"备注字符超长", 'code'=>5]);
            }
            $admin_id=Session::get('ADMIN_ID');
            if(empty($admin_id))
            {
                return json_encode(['code' => 102 ,'msg'=>'请重新登陆']);
            }
            //更新数据
            $condition = [
                'remark' => $params['remark'],
                'custom_id' => $params['custom_id'],
                'allot_id' => $admin_id,
                'allot_ip' => $this->request->ip(),
                'allot_time' => time(),
            ];

            $result = AllotRobotService::UpdateB(['id'=>$params['id']],$condition);

            if(empty($result))
            {
                return json_encode(['msg'=>"操作失败，请稍后再试", 'code'=>1]);
            }
            else
            {
                return json_encode(['msg'=>"操作成功", 'code'=>200]);
            }

        }

        $id = $this->request->param('id');
        $info = AllotRobotService::ToInfo($id);
        //客服
        $kf =  RoleUserService::RList(['field'=>'a.user_id,u.user_nickname','pageSize'=>0]);
        //var_dump($kf);die;
        $this->assign('anchor', $kf['data']);
        $this->assign('info', $info);
        return $this->fetch();
    }


    /**
     * 查看机器人详情
     * @throws
     */
    public function ViewDetails(){
        $id = $this->request->param('user_id');
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
     * 修改机器人信息
     * @throws
     */
    public function editUserInfo(){
        if($this->request->isAjax()){
            $validate = new Validate([
                'new_album_dir' => 'require', //头像
                'user_nickname' => 'require|max:50',//昵称
                'mobile' => [
                    'number',
                    'regex' => '(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$', //验证手机号
                ],
                'sex' => 'require',
                'age' => 'require|number|between:18,80',
                'signature' => 'max:255',
                'address' => 'max:100',
                'province_name' => 'require',
                'city_name' => 'require',
                'qq' => 'number',
            ]);

            $validate->message([
                'new_album_dir.require' => '头像不能为空',
                'user_nickname.require' => '请输入用户昵称',
                'user_nickname.max' => '昵称不能超过50个字符',
                'mobile.require' => '手机号不能为空',
                'mobile.number' => '手机号必须为数字',
                'upload_image.require' => '头像不能为空',
                'age.number' => '年龄必须为正整数',
                'age.between' => '年龄必须在18到80之间',
                'signature.max' => '签名不能超过255个字符',
                'signature.address' => '地址不能超过100个字符',
                'province_name.require' => '省份不能为空',
                'city_name.require' => '城市不能为空',
                'qq.number' => 'QQ号必须为数字',
            ]);

            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);

            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            //更改头像字段 转化日期时间为int类型
            $params['avatar'] = $params['new_album_dir'];
            if(!empty($params['singledatePicker']) && strtotime($params['singledatePicker'])){
                $params['vip_expire_time'] = strtotime($params['singledatePicker']);
            }

            //查询用户原数据
            $info = UserMemberService::ToInfo(['id'=>$params['id']],'*');
            if(!$info){
                return json_encode(['msg' => '用户数据有误', 'code'=> 0]);
            }else{
                //匹配新数据和原数据 不一致时才修改
                $condition = [];
                foreach ($params as $k1 => $v1){
                    foreach ($info as $k2 => $v2){
                        if($k1 == $k2){
                            if(gettype($v2) == 'integer'){ //获取查询信息变量的类型 integer需要转化类型入库
                                if((int)$v1 != $v2){
                                    $condition[$k1] = (int)$v1;
                                }
                            }else{
                                if($v1 != $v2){
                                    $condition[$k1] = $v1;
                                }
                            }
                        }
                    }
                }
            }

            //var_dump($condition);die;

            //省市区
            isset($params['province_name']) && $condition['province_name'] = $params['province_name'];
            isset($params['city_name']) && $condition['city_name'] = $params['city_name'];
            isset($params['district_name']) && $condition['district_name'] = $params['district_name'];
            if(empty($condition)){
                return json_encode(['code'=>200,'msg'=>'保存成功,无修改']);
            }else{
                if ($info['city_name'] != $condition['city_name']) {
                    // 获取该城市的任意一个位置的坐标
                    $posRes = $this->getVicinityPos($condition['city_name']);
                    if ($posRes == false) {
                        return json_encode(['code' => 0, 'msg' => '获取附近poi失败，请检查错误日志并重新操作']);
                    }
                    $posid = array_rand($posRes['results'], 1);
                    $condition['longitude'] = $posRes['results'][$posid]['location']['lng'];
                    $condition['latitude'] = $posRes['results'][$posid]['location']['lat'];
                }

                $res = UserService::UpdateData(['id'=>$params['id']],$condition);
                if($res){
                    return json_encode(['code'=>200,'msg'=>'修改成功']);
                }else{
                    return json_encode(['code'=>0,'msg'=>'修改失败']);
                }
            }
        }
        $uid = $this->request->param('uid');
        $allot_user_info = UserService::ToInfo(['id'=>$uid]);
        if(empty($allot_user_info)){
            return $this->error('该数据不存在!');
        }
        $allot_user_info['avatar_fullurl'] = !empty($allot_user_info['avatar']) ? MaterialService::getFullUrl($allot_user_info['avatar']) : '';

        $allot_user_info['vip_expire_time'] = !empty($allot_user_info['vip_expire_time']) ? date("Y-m-d H:i:s",$allot_user_info['vip_expire_time']) : '';

        //var_dump($allot_user_info);.
        $this->assign('allot_user_info',$allot_user_info);
        return $this->fetch('allot_user_edit');
    }

    /**
     * 资源修改
     * @throws
     */
    public function SourceEdit(){
        $param = $this->request->param();
        $txyunOption = cmf_get_option('trtc');
        switch ($param['type']){
            case 1://相册
                //获取相册路径
                $album_info = UserService::ToInfo(['id'=>$param['id']],'album',-1);
                //转化成数组
                $album_array = json_decode(htmlspecialchars_decode($album_info));
                //截掉域名
                if (preg_match('/^http/', $param['url'])) {
                    $url = substr($param['url'],strlen($txyunOption['cosCdn']));
                }else{
                    return json_encode(['msg'=>'资源地址有误','code'=>0]);
                }
                //获取key
                $album_array_key = array_search($url,$album_array);
                //替换当前url
                $album_array[$album_array_key] = $param['new_url'];
                //重组数组、json、转义
                $album_str = htmlspecialchars(json_encode(array_values($album_array)));
                //修改用户数据
                $res = UserService::UpdateData(['id'=>$param['id']],['album'=>$album_str]);
                LogsService::addRecord(5,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"修改用户id为{$param['id']}的个人相册",json_encode($param));
                $condition = [
                   'user_id' => $param['id'],
                   'class_id' => 1,
                   'bucket' => 'zhibo005',
                   'object' => $param['new_url'],
                   'mime_type' => 'image',
                   'status' => 2,
                   'size' =>  $param['size'],
                   'create_time' => time(),
                ];
                $oss = ResourcesService::AddInfo($condition);
                if($res && $oss){
                    return json_encode(['msg'=>'修改成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'修改失败','code'=>0]);
                }
                //return json_encode(['msg'=>'123', 'code'=>200, 'data'=>$album_array]);
                break;
            case 2://视频
                //获取视频路径
                $video_info = UserService::ToInfo(['id'=>$param['id']],'video',-1);
                //转化成数组
                $video_array = json_decode(htmlspecialchars_decode($video_info));
                //截掉域名
                if (preg_match('/^http/', $param['url'])) {
                    $url = substr($param['url'],strlen($txyunOption['cosCdn']));
                }else{
                    return json_encode(['msg'=>'资源地址有误','code'=>0]);
                }
                //获取key
                $video_array_key = array_search($url,$video_array);
                //替换当前url
                $video_array[$video_array_key] = $param['new_url'];
                //重组数组、json、转义
                $video_str = htmlspecialchars(json_encode(array_values($video_array)));
                //修改用户数据
                $res = UserService::UpdateData(['id'=>$param['id']],['video'=>$video_str]);
                LogsService::addRecord(5,'/'.Request::instance()->controller().'/'.Request::instance()->action(),"修改用户id为{$param['id']}的个人视频",json_encode($param));
                $condition = [
                    'user_id' => $param['id'],
                    'class_id' => 2,
                    'bucket' => 'zhibo005',
                    'object' => $param['new_url'],
                    'mime_type' => 'video',
                    'status' => 2,
                    'size' =>  $param['size'],
                    'create_time' => time(),
                ];
                $oss = ResourcesService::AddInfo($condition);
                if($res && $oss){
                    return json_encode(['msg'=>'视频修改成功','code'=>200]);
                }else{
                    return json_encode(['msg'=>'视频修改失败','code'=>0]);
                }
                break;
            default:
                return json_encode(['msg'=>'无效操作', 'code'=>0]);
                break;
        }
    }

    /**
     * 多选框分配客服
     * @return false|string
     * @throws Exception
     */
    public function BatchAll(){
        $param = $this->request->param();
        if(empty($param['user_id'])){
            return json_encode(['code'=>0,'msg'=>'客服必须选择']);
        }
        if(empty($param['ids'])){
            return json_encode(['code'=>0,'msg'=>'多选框不能为空']);
        }
        $ids = substr($param['ids'],0,strlen($param['ids'])-1);
        $res = AllotRobotService::UpdateB("id in({$ids})",['custom_id'=>$param['user_id']]);
        if($res){
            return json_encode(['code'=>200,'msg'=>'批量分配成功']);
        }else{
            return json_encode(['code'=>0,'msg'=>'批量分配失败']);
        }
    }

    /**
     * 机器人收费设置
     * @return mixed|string
     * @throws Exception
     */
    public function UserSetting(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(!isset($param['user_id']) || !isset($param['open_speech']) || !isset($param['open_video']) || !isset($param['speech_cost']) || !isset($param['video_cost']) ){
                return json_encode(['code'=>0, 'msg'=> '参数错误,请联系管理员']);
            }
            if(!is_numeric($param['video_cost']) || !is_numeric($param['speech_cost'])){
                return json_encode(['code'=>0, 'msg'=> '收费必须为数字']);
            }
            if($param['video_cost'] > 80 || $param['speech_cost'] > 80){
                return json_encode(['code'=>0, 'msg'=> '视频收费或语音收费不能大于80金币']);
            }
            $condition = [
                'user_id' => intval($param['user_id']),
                'open_speech' => intval($param['open_speech']),
                'open_video' => intval($param['open_video']),
                'speech_cost' => intval($param['speech_cost']),
                'video_cost' => intval($param['video_cost']),
            ];
            $is_setting = UserSettingService::ToInfo(['user_id'=>$param['user_id']],'user_id,open_speech,open_video,speech_cost,video_cost');
            if(empty($is_setting)){//添加数据
                $condition['user_id'] = $param['user_id'];
                $res = UserSettingService::AddInfo($condition);
                if($res){
                    return json_encode(['code'=>200, 'msg'=> '提交成功']);
                }
            }else{//修改数据
                foreach ($is_setting as $k=>$v){
                    if($v == $condition[$k]){
                        unset($condition[$k]);
                    }
                }
                if(empty($condition)){
                    return json_encode(['code'=>201, 'msg'=> '数据没有修改,无需提交']);
                }
                $res = UserSettingService::UpdateInfo(['user_id'=>$param['user_id']],$condition);
                if($res){
                    return json_encode(['code'=>200, 'msg'=> '提交成功']);
                }
            }

            return json_encode(['code'=>200, 'msg'=>'提交失败', 'data'=>$condition]);
        }
        $param = $this->request->param();
        if(empty($param['user_id'])){
            return '参数错误,用户id不存在';
        }
        $user_setting = UserSettingService::ToInfo(['user_id'=>$param['user_id']]);
        if(empty($user_setting)){
            $user_setting = UserMemberService::ToInfo(['id'=>$param['user_id']],'user_nickname,id user_id');
        }else{
            $user_setting['user_nickname'] = UserMemberService::ToInfo(['id'=>$param['user_id']],'user_nickname',-1);
        }

        $this->assign('user_setting',$user_setting);
        return $this->fetch();
    }

    /**
     * 获取一个坐标位置
     * @param string $city 城市名称
     * @return bool|mixed
     * @throws Exception
     */
    private function getVicinityPos($city)
    {
        // 获取定位配置
        $position = cmf_get_option('position');

        $url = 'http://api.map.baidu.com/place/v2/search';
        $aParam = [
            'ak' => $position['baidu_web_key'], // 请求服务权限标识 (测试使用：hjzlRMegkSsXd4F8iQfdpXKiaHA4SodE)
            'query' => '小区', // 查询关键字 不同关键字间以$符号分隔
            'tag' => '住宅区', // 检索分类偏好，与q组合进行检索，多个分类以","分隔
            'region' => $city,
            'output' => 'json',
            'coord_type' => 'gcj02ll',
            'ret_coordtype' => 'gcj02ll',
            'page_size' => 20, // 单次召回POI数量，默认为10条记录，最大返回20条。多关键字检索时，返回的记录数为关键字个数*page_size。
            'page_num' => mt_rand(0,10) // 分页页码，默认为0,0代表第一页，1代表第二页，以此类推
        ];
        $url = $url . '?' . http_build_query($aParam);

        try {
            $result = file_get_contents($url);
            $aResult = json_decode($result, true);
            if (! isset($aResult['status']) || $aResult['status'] !== 0) {
                Log::write(sprintf('%s：调用百度地图获取附近位置失败：%s', __METHOD__, var_export($result,true)),'error');
                return false;
            }

            return $aResult;

        } catch (Exception $e) {
            Log::write(sprintf('%s：调用百度地图获取附近位置系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('调用百度地图获取附近位置系统异常:' . $e->getMessage());
        }
    }


    /**
     * 编辑相册视频
     * @throws
     */
    public function SettingSourcePopup(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            $arr = [];
            for ($i = 0; $i < 9; $i++){
                if(!empty($param['album']) && !empty($param['album'][$i])){
                    $arr['album'][] = $param['album'][$i];
                }
                if(!empty($param['video']) && !empty($param['video'][$i])){
                    $arr['video'][] = $param['video'][$i];
                }
            }
            $condition = [
                'album' => empty($arr['album']) ? '' :htmlspecialchars(json_encode($arr['album'])),
                'video' => empty($arr['video']) ? '' :htmlspecialchars(json_encode($arr['video'])),
            ];
            $info = UserMemberService::ToInfo(['id'=>$param['id']],'album,video');
            if($condition['album'] == $info['album']){
                unset($condition['album']);
            }
            if($condition['video'] == $info['video']){
                unset($condition['video']);
            }
            if(empty($condition)){
                return json_encode(['code'=>200, 'msg'=>'无更改']);
            }
            $result_i = UserMemberService::UpdateInfo(['id'=>$param['id']],$condition);
            if($result_i){
                return json_encode(['code' => 200, 'msg' => '保存成功']);
            }else{
                return json_encode(['code' => 0, 'msg' => '保存失败']);
            }
        }
        $id = $this->request->param('id');
        $info = UserMemberService::ToInfo(['id'=>$id],'album,video,user_nickname,id');
        if(!empty($info['album'])){
            $info['album'] = json_decode(htmlspecialchars_decode($info['album']),true);
        }
        if(!empty($info['video'])){
            $info['video'] = json_decode(htmlspecialchars_decode($info['video']),true);
        }

        $this->assign('info',$info);
        return $this->fetch('dropify');
    }


    /**
     * 相册/视频上传方法--停止使用，coase ,2019-07-20
     * @throws
     */
//    public function CommonUploadFile(){
//        //ajax请求 文件上传
//        if($this->request->isAjax()){
//            //请求参数
//            $param = $this->request->file();
//            //生成随机code
//            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//            for ($i = 0, $code = '', $lc = strlen($chars)-1; $i < 5; $i++) {
//                $code .= $chars[mt_rand(0, $lc)];
//            }
//
//            //文件是否上传
//            if(isset($param['file_upload'])){//相册
//                //文件上传对象
//                $file = $this->request->file('file_upload');
//                //获取文件上传信息 数组
//                $file_info =  $file->getInfo();
//                //文件名使用随机code
//                $time = date("YmdHis",time());
//                $new_file_name = $code.$time.strrchr($file_info['name'],'.');
//                //要使用的完整的文件路径
//                $save_path = 'zhibo/'.date("Y",time()).'/'.date("m",time()).'/'.date('d',time()).'/'.$new_file_name;
//                //上传至阿里云服务器
//                $res = UploadService::uploadObject(fopen($file_info['tmp_name'], 'rb'), $save_path);
//                //上传成功后 返回data
//                if($res != false){
//                    $data = [
//                        'type' => 1, // 1=>图片  2=>文件
//                        'save_name' => $new_file_name,
//                        'save_path' => $save_path,
//                    ];
//                    return json_encode(['code'=>1, 'data'=>$data]);
//                }else{
//                    return  json_encode(['code'=>0, 'msg'=>'上传失败']);
//                }
//            }elseif (isset($param['video'])){ //视频
//                //文件上传对象
//                $file = $this->request->file('video');
//                //获取文件上传信息 数组
//                $file_info =  $file->getInfo();
//                //文件名使用随机code
//                $time = date("YmdHis",time());
//                $new_file_name = $code.$time.strrchr($file_info['name'],'.');
//                //要使用的完整的文件路径
//                $save_path = 'zhibo/'.date("Y",time()).'/'.date("m",time()).'/'.date('d',time()).'/'.$new_file_name;
//                //上传至阿里云服务器
//                $res = UploadService::uploadObject(fopen($file_info['tmp_name'], 'rb'), $save_path);
//                //上传成功后 返回data
//                if($res != false){
//                    $data = [
//                        'type' => 2, // 1=>图片  2=>文件
//                        'save_name' => $new_file_name,
//                        'save_path' => $save_path,
//                        'abs_path' => MaterialService::getFullUrl($save_path),
//                    ];
//                    return json_encode(['code'=>1, 'data'=>$data]);
//                }else{
//                    return  json_encode(['code'=>0, 'msg'=>'上传失败']);
//                }
//            }else{
//                return  json_encode(['code'=>0, 'msg'=>'网络异常']);
//            }
//        }else{
//            $this->error('错误的访问类型');
//        }
//    }



}
