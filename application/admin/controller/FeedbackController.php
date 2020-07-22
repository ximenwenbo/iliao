<?php
/**
 * 用户反馈管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\FeedbackService;
use cmf\controller\AdminBaseController;
use think\Exception;
use think\Request;
use think\Db;

class FeedbackController extends AdminBaseController
{
    /**
     * 用户反馈列表
     * @author zjy
     * @throws Exception
     */
    public function FeedbackList()
    {
        return $this->fetch('index');
    }

    /**
     * 列表ajax
     * @throws Exception
     */
    public function ListAjax()
    {
        $params = $this->request->param();
        $condition = [
          'keywords' => empty($params['data']['keywords']) ? '' : $params['data']['keywords'],
          'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
          'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
          'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
          'sortField' => 'id',
          'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
          'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
        ];

        //调用列表方法
        $model = new FeedbackService();
        $result = $model->FeedbackList($condition);
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
                $opera = '<button type="button" class="btn btn-outline btn-danger btn-sm " id="authDel">
                             <i class="icon wb-warning" aria-hidden="true"></i> 删除
                         </button>';

                $filed =  [
                    'id' => $val['id'],
                    'user_id' => $val['user_id'],
                    'user_nickname' => $val['user_nickname'],
                    'content' => $val['content'],
//                    'status' => $val['status']==1?'显示':'不显示',
                    "create_time"=> $val['create_time'],
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
     * 资源列表 - 伪删除数据
     * @author zjy
     * @throws
     */
    public function DelInfo()
    {
        $id = Request::instance()->post('id');
        if(empty($id))
        {
            return json_encode(['msg'=>'数据不存在','code'=>0]);
        }
        if (Db::name('feedback')->where('id', $id)->value('status') != 1) {
            return json_encode(['msg'=>'已经被删除了','code'=>0]);
        }

        $result = Db::name('feedback')->where('id', $id)->update(['status' => 0]);
        if($result){
            return json_encode(['msg'=>'删除成功','code'=>200]);
        }else{
            return json_encode(['msg'=>'删除失败','code'=>0]);
        }

    }
}
