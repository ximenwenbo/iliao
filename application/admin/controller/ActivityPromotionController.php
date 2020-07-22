<?php
/**
 * 活动推广
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\ActivityPromotionService;
use app\admin\service\MaterialService;
use cmf\controller\AdminBaseController;
use think\Validate;


class ActivityPromotionController extends AdminBaseController
{
    /**
     * 推广配置列表
     * @author zjy
     * @throws
     */
    public function IndexList()
    {
        //table表单请求数据
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
                'live_id' => isset($params['data']['live_id']) ? $params['data']['live_id'] : '',
                /*'status' => isset($params['data']['status']) ? $params['data']['status'] : 1,*/
                'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
                'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];

            //调用列表方法
            $result = ActivityPromotionService::RList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-info btn-outline btn-sm" onclick="editPopup('.$value['id'].')">
                            <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                        </button>
                        <button type="button" class="btn btn-outline btn-danger btn-sm " id="delete-btn">
                            <i class="icon wb-trash" aria-hidden="true"></i> 删除
                        </button>
                        ';

                    $filed =  [
                        'id' => $value['id'],
                        'title' => $value['title'],
                        'desc' => $value['desc'],
                        'thumb' => '<img src="'.MaterialService::getFullUrl($value['thumb']).'" width="50px" height="50px" />',
                        'other' =>  $value['other'],
                        'type' => ActivityPromotionService::typeList($value['type']),
                        'status' => ActivityPromotionService::statusList($value['status']),
                        'push_type' => ActivityPromotionService::PushTypeList($value['push_type']),
                        'push_time' => date("Y-m-d H:i",$value['push_time']),
                        'create_time' => date("Y-m-d H:i",$value['create_time']),
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
        return $this->fetch('index');
    }

    /**
     * 添加推广配置
     * @author zjy
     * @throws
     */
    public function AddInfo()
    {
        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                'title' => 'require',
                'desc' => 'require',
                'thumb' => 'require',
                'type' => 'require',
                'other' => 'require',
                'push_type' => 'require',
                'push_time' => 'require',
            ]);
            $validate->message([
                'title.require' => '标题不能为空',
                'thumb.require' => '缩略图不能为空',
                'desc.require' => '描述不能为空',
                'type.require' => '类型不能为空',
                'other.require' => '场景参数不能为空',
                'push_type.require' => '推送类型不能为空',
                'push_time.require' => '推送时间不能为空',

            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            if($params['type'] == 1 && ! preg_match("/^[1-9][0-9]*$/",$params['other'])){
                return json_encode(['msg' => '用户uid必须为正整数', 'code'=> 0]);
            }

            if($params['type'] == 2 && ! preg_match("/^(https|http)?:\/\/[^\s]+$/",$params['other'])){
                return json_encode(['msg' => '请填写完整的url,包含http', 'code'=> 0]);
            }

            $condition = [
                'title' => $params['title'],
                'desc' => $params['desc'],
                'thumb' => $params['thumb'],
                'type' => $params['type'],
                'other' => $params['other'],
                'push_type' => $params['push_type'],
                'push_time' => strtotime($params['push_time']),
                'create_time' => time(),
            ];
            $res = ActivityPromotionService::AddData($condition);
            if($res){
                return json_encode(['code' => 200, 'msg' => '添加成功']);
            }else{
                return json_encode(['msg' => '添加失败', 'code'=> 0]);
            }
        }
        $push_type = ActivityPromotionService::PushTypeList();
        $this->assign('push_type',$push_type);
        return $this->fetch('add');
    }

    /**
     * 编辑
     * @throws
     */
    public function EditInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param('data');
            parse_str(urldecode(htmlspecialchars_decode($param)),$params);
            $validate = new Validate([
                'id' => 'require',
                'title' => 'require',
                'desc' => 'require',
                'thumb' => 'require',
                'status' => 'require',
                'type' => 'require',
                'other' => 'require',
                'push_type' => 'require',
                'push_time' => 'require',
            ]);
            $validate->message([
                'id.require' => 'id不能为空',
                'title.require' => '标题不能为空',
                'thumb.require' => '缩略图不能为空',
                'status.require' => '状态不能为空',
                'desc.require' => '描述不能为空',
                'type.require' => '类型不能为空',
                'other.require' => '场景参数不能为空',
                'push_type.require' => '推送类型不能为空',
                'push_time.require' => '推送时间不能为空',

            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }
            if($params['type'] == 1 && ! preg_match("/^[1-9][0-9]*$/",$params['other'])){
                return json_encode(['msg' => '用户uid必须为正整数', 'code'=> 0]);
            }

            if($params['type'] == 2 && ! preg_match("/^(https|http)?:\/\/[^\s]+$/",$params['other'])){
                return json_encode(['msg' => '请填写完整的url,包含http', 'code'=> 0]);
            }



            $condition = [
                'title' => $params['title'],
                'desc' => $params['desc'],
                'thumb' => $params['thumb'],
                'type' => $params['type'],
                'other' => $params['other'],
                'status' => $params['status'],
                'push_type' => $params['push_type'],
                'push_time' => strtotime($params['push_time']),
                'update_time' => time(),
            ];

            $res = ActivityPromotionService::UpdateB(['id'=>$params['id']],$condition);
            if($res){
                return json_encode(['code' => 200, 'msg' => '保存成功']);
            }else{
                return json_encode(['code' => 0, 'msg' => '保存失败']);
            }
        }
        $id = $this->request->param('id',0);
        if(empty($id)){
            return $this->error('参数有误');
        }
        $info = ActivityPromotionService::ToInfo(['id'=>$id]);
        $info['abs_thumb'] = MaterialService::getFullUrl($info['thumb']);
        $push_type = ActivityPromotionService::PushTypeList();
        $this->assign('push_type',$push_type);
        $this->assign('info',$info);
        return $this->fetch('edit');
    }

    /**
     * 删除
     * @throws
     */
    public function DeleteInfo(){
        $param = $this->request->param();

        if(empty($param['id']))
        {
            return json_encode(["status"=>0, "msg"=>"参数错误",]);
        }
        $condition = [
            'status' => -99,
            'update_time' => time(),
        ];
        $result = ActivityPromotionService::UpdateB(['id'=>$param['id']],$condition);

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
