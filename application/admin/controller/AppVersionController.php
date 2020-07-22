<?php
/**
 * app版本控制管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\MaterialService;
use app\admin\service\AppVersionService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Request;
use think\response\Json;
use app\admin\service\file\UploadService;

class AppVersionController extends AdminBaseController
{
    /**
     * 机器人列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        return $this->fetch('index');
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
            'update_status' => empty($params['data']['update_status']) ? -1 : $params['data']['update_status'],
            'system_type' => empty($params['data']['system_type']) ? -1 : $params['data']['system_type'],
            'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
            'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
            'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
            'sortField' =>  empty($params['sortField']) ? 0 : $params['sortField'],
            'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
            'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];
        $result = AppVersionService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '<button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$val['id'].')">
                                    <i class="icon wb-edit" aria-hidden="true" ></i> 修改
                                 </button>';

                switch ($val['status']){
                    case 0:
                        $status = '<span style="color:red;">无效</span>';
                        break;
                    case 1:
                        $status = '<span style="color:green;">有效</span>';
                        break;
                    default:
                        $status = '未知';
                        break;
                }

                $filed =  [
                    'icon' => '<i class="icon wb-dropright" aria-hidden="true"></i>',
                    'id' => $val['id'],
                    'app_version' => $val['app_version'],
                    'app_class' => $val['app_class'] == 1 ? '用户端' : '客服端',
                    'system_type' => $val['system_type'] == 1 ? 'Android' : 'ios',
                    'update_msg' => $val['update_msg'],
                    'update_status' => $val['update_status'] == 1 ? '<span style="color: #807d7c">否</span>' : '<span style="color: #0016b0">是</span>',
                    'sdk_url' => MaterialService::getFullUrl($val['sdk_url']),
                    'old_url' => $val['sdk_url'],
                    'status' => $status,
                    "create_time"=> date("Y-m-d H:i",$val['create_time']),
                    "published_time"=>  empty($val['published_time']) ? '无' : date("Y-m-d H:i",$val['published_time']),
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
     * 新增APP版本
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            if(!isset($param['app_class']) || !isset($param['app_version'])|| !isset($param['status'])|| !isset($param['update_status'])|| !isset($param['system_type'])){
                return json_encode(['msg'=>'必填项不能为空', 'code' => 0]);
            }
            if(empty($param['app_version'])){
                return json_encode(['msg'=>'版本号不能为空', 'code' => 0]);
            }
            if(!strtotime($param['published_time'])){
                return json_encode(['msg'=>'发布时间格式错误', 'code' => 0]);
            }else{
                $param['published_time'] = strtotime($param['published_time']);
            }
            if(mb_strlen($param['sdk_url']) > 255){
                return json_encode(['msg'=>'下载地址过长', 'code' => 0]);
            }
            if(mb_strlen($param['update_msg']) > 255){
                return json_encode(['msg'=>'版本更新说明字符过长', 'code' => 0]);
            }
            $param['create_time'] = time();
            $res = AppVersionService::AddInfo($param);
            if($res){
                return json_encode(['msg'=>'新增成功', 'code' => 200]);
            }else{
                return json_encode(['msg'=>'新增失败', 'code' => 0]);
            }

        }
        $new_date = date("Y-m-d H:i",time());
        $this->assign('new_date',$new_date);
        return $this->fetch('add');
    }

    /**
     * 修改APP版本
     * @throws
     */
    public function edit()
    {
        //post数据接收
        if($this->request->isAjax()){
            //数据验证
            $param = $this->request->param();
            if(!isset($param['id']) || empty($param['id']))
            {
                return json_encode(['msg'=>"参数错误，请稍后再试", 'code'=>1]);
            }
            if(!isset($param['app_class']) || !isset($param['app_version'])|| !isset($param['status'])|| !isset($param['update_status'])|| !isset($param['system_type'])){
                return json_encode(['msg'=>'必填项不能为空', 'code' => 0]);
            }
            if(empty($param['app_version'])){
                return json_encode(['msg'=>'版本号不能为空', 'code' => 0]);
            }
            if(!strtotime($param['published_time'])){
                return json_encode(['msg'=>'发布时间格式错误', 'code' => 0]);
            }else{
                $param['published_time'] = strtotime($param['published_time']);
            }
            if(mb_strlen($param['sdk_url']) > 255){
                return json_encode(['msg'=>'下载地址过长', 'code' => 0]);
            }
            if(mb_strlen($param['update_msg']) > 255){
                return json_encode(['msg'=>'版本更新说明字符过长', 'code' => 0]);
            }
            $res = AppVersionService::UpdateInfo(['id'=>$param['id']],$param);
            if($res){
                return json_encode(['msg'=>'修改成功', 'code' => 200]);
            }else{
                return json_encode(['msg'=>'修改失败', 'code' => 0]);
            }
        }

        $id = $this->request->param('id');
        $info = AppVersionService::ToInfo($id);
        $new_date = date("Y-m-d H:i",time());
        $info['published_time'] = !empty($info['published_time']) ? date('Y-m-d H:i',$info['published_time']) : $new_date;
        //var_dump($info);die;

        $this->assign('new_date', $new_date);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DelInfo()
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
    }

    /**
     * 图片上传到阿里云oss
     * @return false|string|Json
     */
    public function uploadFile(){
        //ajax请求 文件上传
        if($this->request->isAjax()){
            //请求参数
            $param = $this->request->file();
            //生成随机code
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            for ($i = 0, $code = '', $lc = strlen($chars)-1; $i < 5; $i++) {
                $code .= $chars[mt_rand(0, $lc)];
            }
            //文件是否上传
            if(isset($param['file'])){
                //文件上传对象
                $file = $this->request->file('file');
                //获取文件上传信息 数组
                $file_info =  $file->getInfo();
                //文件名使用随机code
                $time = date("YmdHis",time());
                $new_file_name = $code.$time.strrchr($file_info['name'],'.');
                //要使用的完整的文件路径
                $save_path = 'upload/apk/' . $new_file_name;
                //上传至云存储
                $res = UploadService::uploadObject(fopen($file_info['tmp_name'], 'rb'), $save_path);
                //上传成功后 返回data
                if($res != false){
                    $data = [
                        'type' => 2, // 1=>图片  2=>文件
                        'save_name' => $new_file_name,
                        'save_path' => $save_path,
                    ];
                    return json_encode(['code'=>1, 'data'=>$data]);
                }else{
                    return  json_encode(['code'=>0, 'msg'=>'上传失败']);
                }
            }else{
                return  json_encode(['code'=>0, 'msg'=>'网络异常']);
            }
        }else{
            $this->error('错误的访问类型');
        }
    }

}
