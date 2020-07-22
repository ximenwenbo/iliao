<?php
/**
 * 直播间管理员
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\LiveManagerService;
use app\admin\service\UserMemberService;
use cmf\controller\AdminBaseController;

class LiveManagerController extends AdminBaseController
{
    /**
     * 记录列表
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
                'start_time' => isset($params['data']['startDate']) ? $params['data']['startDate'] : '',
                'end_time' => isset($params['data']['endDate'])  ? $params['data']['endDate'] : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];
            $result = LiveManagerService::RList($condition);

            //调用列表方法
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-outline btn-danger btn-sm " id="DelOne">
                                <i class="icon wb-trash" aria-hidden="true"></i> 删除
                         </button>
                        ';
                    $filed =  [
                        'id' => $value['id'],
                        'live_uid' => $value['user_nickname'].' <small>( '.$value['live_uid'].' )</small>',
                        'user_id' => UserMemberService::ToInfo(['id'=>$value['user_id']],'user_nickname',-1).' <small>( '.$value['user_id'].' )</small>',
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
        return $this->fetch();
    }



    /**
     * 删除一条数据
     * @throws
     */
    public function DelOne(){
        $id = $this->request->param('id',0);
        if(empty($id)){
            return json_encode(['code' => 0, 'msg' => '网络异常,请稍后重试']);
        }
        $res = LiveManagerService::DeleteInfo(['id'=>$id]);
        if($res){
            return json_encode(['code' => 200, 'msg' => '删除成功']);
        }else{
            return json_encode(['code' => 0 ,'msg' => '删除失败']);
        }
    }

}
