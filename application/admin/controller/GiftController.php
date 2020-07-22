<?php
/**
 * 礼物管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\GiftService;
use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class GiftController extends AdminBaseController
{
    /**
     * 礼物列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        $type = GiftService::typeList();
        $this->assign('type', $type);
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
            'type' => empty($params['data']['type']) ? '' : $params['data']['type'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' => empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = GiftService::RList($condition);
        //var_dump($domain=$this->request->domain());die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $domain=$this->request->domain();
                $icon_img = MaterialService::getFullUrl($val['icon_img']);
                $effect_img = MaterialService::getFullUrl($val['effect_img']);
                $icon_img =  '<div class="gallery-wrapper">
                                        <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$icon_img."'".')">
                                            <img src="'.$icon_img.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                        </a>
                                    </div>';
                $effect_img = '<div class="gallery-wrapper">
                                        <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$effect_img."'".')">
                                            <img src="'.$effect_img.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
                                        </a>
                                    </div>';
                $opera = '<button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$val['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                                 </button>
                                 <button type="button" class="btn btn-outline btn-danger btn-sm " id="authDel">
                                        <i class="icon wb-warning" aria-hidden="true"></i> 删除
                                     </button>';
                switch ($val['status']){
                    case 0:
                        $status = '<span style="color:red;">停止使用</span>';
                        break;
                    case 1:
                        $status = '<span style="color:green;">使用中</span>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }
                $filed =  [
                    'id' => $val['id'],
                    'sort' => $val['sort'],
                    'uni_code' => $val['uni_code'],
                    'name' => $val['name'],
                    'icon_img' => $icon_img,
                    'effect_img' => $effect_img,
                    'coin' => $val['coin'],
                    'type' => GiftService::typeList($val['type']),
                    'style' => GiftService::styleList($val['style']),
                    'status' => $status,
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
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
     * 添加礼物
     * @throws
     */
    public function AddGift(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            //数据验证
            if(!isset($param['name']) || empty($param['name'])){
                return json_encode(['code'=>0, 'msg'=>'礼物名称不能为空']);
            }else{
                if(mb_strlen($param['name']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'礼物名称不能超过100个字符']);
                }
            }
            if(!isset($param['coin']) || empty($param['coin'])){
                return json_encode(['code'=>0, 'msg'=>'金币不能为空']);
            }else{
                if(!is_numeric($param['coin']))
                {
                    return json_encode(['code'=>0, 'msg'=>'金币必须是整数']);
                }
            }
            if(!isset($param['gift_thumb_dir']) || empty($param['gift_thumb_dir'])){
                return json_encode(['code'=>0, 'msg'=>'icon图片不能为空']);
            }else{
                if(mb_strlen($param['gift_thumb_dir']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'icon图片名称过长']);
                }
            }
            if(!isset($param['gift_gif_dir']) || empty($param['gift_gif_dir'])){

                return json_encode(['code'=>0, 'msg'=>'gif图片不能为空']);
            }else{
                if(mb_strlen($param['gift_gif_dir']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'gif图片名称过长']);
                }
            }
            if(!is_numeric($param['sort']))
            {
                return json_encode(['code'=>0, 'msg'=>'排序必须是数字']);
            }
            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }
            $condition = [
              'uni_code' => GiftService::RandomUniqueCode(),
              'name' => $param['name'],
              'icon_img' => $param['gift_thumb_dir'],
              'effect_img' => $param['gift_gif_dir'],
              'coin' => $param['coin'],
              'status' => $param['status'],
              'create_time' => time(),
              'remark' => $param['remark'],
              'sort' => $param['sort'],
              'type' => $param['type'],
              'style' => $param['style'],
            ];
            $result = GiftService::AddGift($condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'添加数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'添加数据失败','data'=>$result]);
            }
        }

        //礼物类型
        $type = GiftService::typeList(-1);
        $style = GiftService::styleList(-1);
        $this->assign('style',$style);
        $this->assign('type',$type);
        return $this->fetch('add');
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
        //$new_name = date("YmdHis").$code;
        if(isset($param['icon_img'])){
            //文件上传对象
            $file = $this->request->file('icon_img');
            //获取文件上传信息 数组
            $file_info =  $file->getInfo();
            //文件名使用随机code
            $time = date("YmdHis",time());
            $new_name = $code.$time.strrchr($file_info['name'],'.');
            $file_dir = '/upload/gift/icon/';
            if (!file_exists(ROOT_PATH .'publication' . $file_dir)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir(ROOT_PATH .'publication' . $file_dir, 0777, true);
            }
            $info = $file->rule('uniqid')->move(ROOT_PATH .'publication' . '/'.$file_dir,$new_name);
            $data = [
                'type' => 1,
                'save_name' => $new_name,
                'save_dir' => $file_dir.$info->getSaveName(),
            ];
            return json_encode(['code'=>1, 'data'=>$data]);
        }elseif (isset($param['effect_img'])){
            //文件上传对象
            $file = $this->request->file('effect_img');
            //获取文件上传信息 数组
            $file_info =  $file->getInfo();
            //文件名使用随机code
            $time = date("YmdHis",time());
            $new_name = $code.$time.strrchr($file_info['name'],'.');
            $file_dir = '/upload/gift/effect/';
            if (!file_exists(ROOT_PATH .'publication' . $file_dir)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir(ROOT_PATH .'publication' . $file_dir, 0777, true);
            }
            $info = $file->rule('uniqid')->move(ROOT_PATH .'publication' . '/'.$file_dir,$new_name);
            if($info){
                $data = [
                    'type' => 2,
                    'save_name' => $new_name,
                    'save_dir' =>  $file_dir.$info->getSaveName(),
                ];

                return json_encode(['code'=>1, 'data'=>$data]);
            }
        }else{
            return  json_encode(['code'=>0, 'msg'=>'网络异常']);
        }
    }



    /**
     * 礼物编辑
     * @throws
     */
    public function edit()
    {
        //post数据接收
        if($this->request->isAjax()){
            //数据验证
            $param = $this->request->param();
            if(!isset($param['id']) || empty($param['id'])){
                return json_encode(['code'=>0, 'msg'=>'数据已过期']);
            }
            if(!isset($param['name']) || empty($param['name'])){
                return json_encode(['code'=>0, 'msg'=>'礼物名称不能为空']);
            }else{
                if(mb_strlen($param['name']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'礼物名称不能超过100个字符']);
                }
            }
            if(!isset($param['coin']) || empty($param['coin'])){
                return json_encode(['code'=>0, 'msg'=>'金币不能为空']);
            }else{
                if(!is_numeric($param['coin']))
                {
                    return json_encode(['code'=>0, 'msg'=>'金币必须是整数']);
                }
            }
            if(!isset($param['gift_thumb_dir']) || empty($param['gift_thumb_dir'])){
                return json_encode(['code'=>0, 'msg'=>'icon图片不能为空']);
            }else{
                if(mb_strlen($param['gift_thumb_dir']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'icon图片名称过长']);
                }
            }
            if(!isset($param['gift_gif_dir']) || empty($param['gift_gif_dir'])){

                return json_encode(['code'=>0, 'msg'=>'gif图片不能为空']);
            }else{
                if(mb_strlen($param['gift_gif_dir']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'gif图片名称过长']);
                }
            }
            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }

            if(!is_numeric($param['sort']))
            {
                return json_encode(['code'=>0, 'msg'=>'排序必须是数字']);
            }
            $condition = [
                'name' => $param['name'],
                'icon_img' => $param['gift_thumb_dir'],
                'effect_img' => $param['gift_gif_dir'],
                'coin' => $param['coin'],
                'status' => $param['status'],
                'update_time' => time(),
                'remark' => $param['remark'],
                'sort' => $param['sort'],
                'type' => $param['type'],
                'style' => $param['style'],
            ];
            $result = GiftService::UpdateGift(['id'=>$param['id']],$condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'修改数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'修改数据失败','data'=>$result]);
            }
        }
        $id = $this->request->param('id');
        $info = GiftService::ToInfo($id);
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
        $info['abs_thumb'] = MaterialService::getFullUrl($info['icon_img']);
        $info['abs_gif'] = MaterialService::getFullUrl($info['effect_img']);
        //礼物类型
        $type = GiftService::typeList(-1);
        $style = GiftService::styleList(-1);
        $this->assign('style',$style);
        $this->assign('type',$type);
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function giftDelete()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在",]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
        ];
        $result = GiftService::UpdateGift(['id'=>$id],$condition);
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
