<?php
/**
 * 后台管理员
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\LiveStreamService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;

class LiveStreamController extends AdminBaseController
{
    /**
     * 管理员列表
     * @author zjy
     * @throws
     */
    public function index()
    {
        //table表单请求数据
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
                'status' => isset($params['data']['status']) ? $params['data']['status'] : 1,
                'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
                'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];

            //调用列表方法
            $result = LiveStreamService::RList($condition);

            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-outline btn-danger btn-sm " id="delete-btn">
                            <i class="icon wb-trash" aria-hidden="true"></i> 删除
                        </button>
                        ';

                    $filed =  [
                        'id' => $value['id'],
                        'stream_id' => $value['stream_id'],
                        'option_id' => $value['option_id'],
                        'mix_stream_session_id' => $value['mix_stream_session_id'],
                        'option_class_id' => LiveStreamService::categoryList($value['option_class_id']),
                        'option_type' => LiveStreamService::typeList($value['option_type']),
                        'user_id' => $value['user_nickname'].' <small>( '.$value['user_id'].' )</small>',
                        'start_time' => !empty($value['start_time']) ? date("Y-m-d H:i:s", $value['start_time']) : '',
                        'end_time' => !empty($value['end_time']) ? date("Y-m-d H:i:s", $value['end_time']) : '',
                        'status' => LiveStreamService::statusList($value['status']),
                        'create_time' => $value['create_time'],
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
        $status_arr = LiveStreamService::statusList();
        $this->assign('status_arr',$status_arr);
        return $this->fetch();
    }


    /**
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DeleteInfo()
    {
        $param = $this->request->param();

        if(empty($param['id']))
        {
            return json_encode(["status"=>0, "msg"=>"参数错误",]);
        }
        $condition = [
            'status' => -99,
            'update_time' => date("Y-m-d H:i:s",time()),
        ];
        $result = LiveStreamService::UpdateB(['id'=>$param['id']],$condition);

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
