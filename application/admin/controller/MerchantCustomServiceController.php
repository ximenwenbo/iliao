<?php
/**
 * 用户管理
 * @author zjy
 */
namespace app\admin\controller;


use app\admin\service\MaterialService;
use app\admin\service\MerchantCustomerService;
use app\admin\service\MerchantUserService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;
use think\Db;


class MerchantCustomServiceController extends AdminBaseController
{
    /**
     * 列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => !isset($params['data']['keywords']) ? '' : $params['data']['keywords'],
                'user_status' => !isset($params['data']['user_status']) ? '' : $params['data']['user_status'],
                'daren_status' => !isset($params['data']['daren_status']) ? '' : $params['data']['daren_status'],
                'is_vip' => !isset($params['data']['is_vip']) ? '' : $params['data']['is_vip'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $user_id = $this->getUserId();
            $result = MerchantUserService::UList($condition,$user_id);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $item)
                {
                    $avatar = MaterialService::getFullUrl($item['avatar']);
                    $opera = '
                                </button>&nbsp;&nbsp;&nbsp;'.'<button type="button" class="btn btn-success btn-sm" onclick="sourcePopup('.$item['id'].')">
                                    <i class="icon wb-user" aria-hidden="true" ></i> 查看
                                 </button>';

                    switch ($item['user_status']){
                        case 0:
                            $status = '<span style="color:red;">禁用</span>';
                            break;
                        case 1:
                            $status = '<span style="color:green;">正常</span>';
                            break;
                        default:
                            $status = '未知';
                            break;
                    }
                    $sex = ['保密','男','女'];
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
                        'weixin' => $item['weixin'],
                        'address' => $item['address'],
                        'speech_introduction' => $item['speech_introduction'],
                        'tags' => $item['tags'],
                        'status' => $status,
                        'is_vip' => time() < $item['vip_expire_time'] ? '是' : '否',
                        'vip_expire_time' => empty($item['vip_expire_time']) ? '无' : date("Y-m-d H:i",$item['vip_expire_time']),
                        'daren_status' => MerchantUserService::statusList($item['daren_status']),
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
        $merchant_name = MerchantCustomerService::getMerchantName();
        $this->assign('merchant_name', $merchant_name);
        return $this->fetch();
    }


    /**
     * 查看详情
     * @throws
     */
    public function ViewDetails(){
        $id = $this->request->param('id');
        $info = UserMemberService::ToInfo(['id'=>$id],'album,video,mobile,user_nickname,id,sex,age,address,signature,avatar');
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
        //var_dump($video);
        $info['avatar'] = MaterialService::getFullUrl($info['avatar']);
        $sex_type = ['保密', '男', '女'];
        $info['sex_type'] = isset($sex_type[$info['sex']]) ? $sex_type[$info['sex']] : '';
        $this->assign('user_info',$info);
        $this->assign('album',$album);
        $this->assign('video',$video);
        return $this->fetch();
    }

    /**
     * @return bool|string|array
     * @throws
     */
    public function getUserId(){
        $admin_id = cmf_get_current_admin_id();
        $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
        if($m_id === NULL){
            if($admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                exit('您没有权限查看,请联系超级管理员!');
            }
        }else{
            if($m_id == 0 && $admin_id == 1){
                $uid_arr = Db::name('merchant_customer')->where("status=1")->field('user_id')->select()->toArray();
            }else{
                $m_id = Db::name('merchant_customer')->where("user_id = {$admin_id} and status=1")->value('m_id');
                $uid_arr = Db::name('merchant_customer')->where("m_id = {$m_id} and status=1")->field('user_id')->select()->toArray();
            }
        }

        if(!$uid_arr){
            exit("商户下没有客服");
        }
        $uid_in = '';
        foreach ($uid_arr as $k1 => $v1){
            $uid_in .= $v1['user_id'].',';
        }
        $uid_in = substr($uid_in,0,strlen($uid_in)-1);

        return $uid_in;
    }
}
