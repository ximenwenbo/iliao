<?php
/**
 * 轮播图管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\BannerService;
use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\Session;


class BannerController extends AdminBaseController
{
    /**
     * 礼物列表
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
            'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = BannerService::RList($condition);
        //var_dump($domain=$this->request->domain());die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $domain=$this->request->domain();
                $banner_img = MaterialService::getFullUrl($val['img_url']);
                $banner_img =  '<div class="gallery-wrapper">
                                        <a class="galpop-single" href="javascript:void(0)" onclick="NewGalpop('."'".$banner_img."'".')">
                                            <img src="'.$banner_img.'" class="img-thumbnail" alt="" width="50px" height="50px"/>
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
                        $status = '<span style="color:red;">隐藏</span>';
                        break;
                    case 1:
                        $status = '<span style="color:green;">显示</span>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }
                //编辑人
                $edit_id = Db::name('user')->where(['id'=>$val['edit_id']])->value('user_login');
                $filed =  [
                    'id' => $val['id'],
                    'title' => $val['title'],
                    'img' => $banner_img,
                    'type' => BannerService::typeList($val['type']),
                    'a_url' => empty($val['a_url'])?'无':$val['a_url'],
                    'status' => $status,
                    'sort' => $val['sort'],
                    'admin_id' => $val['user_login'],
                    'admin_ip' => $val['admin_ip'],
                    'update_time' => empty($val['update_time']) ? '无':date("Y-m-d H:i",$val['update_time']),
                    'edit_id' => empty($edit_id) ? '无' : $edit_id,
                    'edit_ip' => empty($val['edit_ip']) ? '无':$val['edit_ip'],
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
            "sortField"=> $condition['sortField'],//排序字段
            "sortType"=> $condition['sortType'],//排序类型
            "total"=> $result['total'],//总记录数
            'pageList'=>$data,//分页数据
            "data"=> $params['data']//表单参数
        ]);
    }

    /**
     * 添加轮播图
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            //数据验证
            if(!isset($param['title']) || empty($param['title'])){
                return json_encode(['code'=>0, 'msg'=>'标题不能为空']);
            }else{
                if(mb_strlen($param['title']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'标题不能超过100个字符']);
                }
            }

            if(!isset($param['type']) || $param['type'] < 0){
                return json_encode(['code'=>0, 'msg'=>'使用位置不能为空']);
            }

            if(!isset($param['sort']) || !is_numeric($param['sort'])){
                return json_encode(['code'=>0, 'msg'=>'排序必须为数字']);
            }else{
                if(!is_numeric($param['sort']))
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }

            if(!isset($param['img_url']) || empty($param['img_url'])){
                return json_encode(['code'=>0, 'msg'=>'图片不能为空']);
            }else{
                if(mb_strlen($param['img_url']) > 255)
                {
                    return json_encode(['code'=>0, 'msg'=>'icon图片名称过长']);
                }
            }
            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }
            $condition = [
                'title' => $param['title'],
                'type' => intval($param['type']),
                'img_url' => $param['img_url'],
                'a_url' => $param['a_url'],
                'sort' => intval($param['sort']),
                'status' => $param['status'],
                'create_time' => time(),
                'remark' => $param['remark'],
                'admin_id' => Session::get('ADMIN_ID'),
                'admin_ip' => $this->request->ip(),
            ];
            $result = BannerService::AddBanner($condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'添加数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'添加数据失败','data'=>$result]);
            }
        }
        $typeList = BannerService::typeList();
        $this->assign('typeList',$typeList);
        return $this->fetch('add');
    }

    /**
     * 图片上传
     * @return false|string
     */
    public function uploadFile(){
        $param = $this->request->file();
        if(isset($param['banner_img'])){
            $file = $this->request->file('banner_img');
            $file_dir = '/upload/banner/';
            if (!file_exists(ROOT_PATH .'publication' . $file_dir)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir(ROOT_PATH .'publication' . $file_dir);
                chmod(ROOT_PATH .'publication' . $file_dir, 0777);
            }
            //保存文件名
            $fileExt = $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $newName = md5(microtime() . mt_rand(1, 9999)) . '.' . $fileExt;
            $file_name = date("YmdHis",time()).rand(0,999999);
            $info = $file->rule('uniqid')->move(ROOT_PATH .'publication'.$file_dir,$file->getInfo()['name'],$file_name);
            $data = [
                'type' => 1,
                'save_name' => $newName,
                'save_dir' => $file_dir.$newName,
            ];
            return json_encode(['code'=>1, 'data'=>$data]);
        }else{
            return  json_encode(['code'=>0, 'msg'=>'网络异常']);
        }
    }

    /**
     * 轮播图编辑
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
            if(!isset($param['title']) || empty($param['title'])){
                return json_encode(['code'=>0, 'msg'=>'标题不能为空']);
            }else{
                if(mb_strlen($param['title']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'标题不能超过100个字符']);
                }
            }

            if(!isset($param['type']) || $param['type'] < 0){
                return json_encode(['code'=>0, 'msg'=>'使用位置不能为空']);
            }

            if(!isset($param['sort']) || empty($param['sort'])){
                return json_encode(['code'=>0, 'msg'=>'排序不能为空']);
            }else{
                if(!is_numeric($param['sort']))
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }

            if(!isset($param['img_url']) || empty($param['img_url'])){
                return json_encode(['code'=>0, 'msg'=>'图片不能为空']);
            }else{
                if(mb_strlen($param['img_url']) > 255)
                {
                    return json_encode(['code'=>0, 'msg'=>'icon图片名称过长']);
                }
            }
            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }

            $condition = [
                'title' => $param['title'],
                'type' => intval($param['type']),
                'img_url' => $param['img_url'],
                'a_url' => $param['a_url'],
                'sort' => intval($param['sort']),
                'status' => $param['status'],
                'update_time' => time(),
                'remark' => $param['remark'],
                'edit_id' => Session::get('ADMIN_ID'),
                'edit_ip' => $this->request->ip(),
            ];
            $result = BannerService::UpdateB(['id'=>$param['id']],$condition);
            if($result){
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'修改数据成功']);
            }else{
                return json_encode(['code'=>0, 'msg'=>'修改数据失败','data'=>$result]);
            }
        }
        $id = $this->request->param('id');
        $info = BannerService::ToInfo($id);
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
        $info['img_url_abs'] = MaterialService::getFullUrl($info['img_url']);
        $typeList = BannerService::typeList();
        $this->assign('typeList',$typeList);
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DeleteInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(["status"=>0, "msg"=>"数据不存在"]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
            'edit_id' => Session::get('ADMIN_ID'),
            'edit_ip' => $this->request->ip(),
        ];
        $result = BannerService::UpdateB(['id'=>$id],$condition);
        if(empty($result))
        {
            return json_encode(["code"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            return json_encode(["code"=>200, "msg"=>"删除成功!",]);
        }

    }

}
