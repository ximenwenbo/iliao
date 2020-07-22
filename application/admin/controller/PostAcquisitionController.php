<?php
/**
 * 用户采集数据
 * @author zjy
 */
namespace app\admin\controller;



use app\admin\service\PostAcquisitionService;
use cmf\controller\AdminBaseController;


class PostAcquisitionController extends AdminBaseController
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
                'cj_status' => !isset($params['data']['cj_status']) ? '' : $params['data']['cj_status'],
                'start_time' => empty($params['data']['startDate']) ? '' : strtotime($params['data']['startDate']),
                'end_time' => empty($params['data']['endDate']) ? '' : strtotime($params['data']['endDate'])+86399,
                'pageSize' => empty($params['pageSize']) ? 10 : $params['pageSize'],
                'sortField' => empty($params['sortField']) ? 1 : $params['sortField'],
                'sortType' => empty($params['sortType']) ? 'desc' : $params['sortType'],
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : 0,
            ];
            $result = PostAcquisitionService::UList($condition);
            //var_dump($result);die;
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $item)
                {
                    $status =  PostAcquisitionService::statusList($item['cj_status']);
                    switch ($item['cj_status']){
                        case 0:
                            $status_div = '<span style="color: silver;">'.$status.'</span>';
                            break;
                        case 1:
                            $status_div = '<span style="color: goldenrod;">'.$status.'</span>';
                            break;
                        case 5:
                            $status_div = '<span style="color: green;">'.$status.'</span>';
                            break;
                        case 10:
                            $status_div = '<span style="color: red;">'.$status.'</span>';
                            break;
                        case -99:
                            $status_div = '<span style="color: brown;">'.$status.'</span>';
                            break;
                        default:
                            $status_div = '暂无状态';
                            break;
                    }

                    if(empty($item['title'])){
                        $title_tex = $item['content'];
                        $title = mb_strlen($item['content']) > 20 ? mb_substr($item['content'],0,20,'utf-8').'.....' : $item['content'];
                    }else{
                        $title_tex = $item['title'];
                        $title = mb_strlen($item['title']) > 20 ? mb_substr($item['title'],0,20,'utf-8').'......' : $item['title'];
                    }
                    $filed =  [
                        'id' => $item['id'],
                        'account_uuid' => $item['account_uuid'],
                        'nickname' => $item['nickname'],
                        'avatar' => '<img src="'.$item['avatar'].'" width="50px" height="50px"/>',
                        'title' => '<span title="'.$title_tex.'">'.$title.'</span>',
                        'cj_review_untrans_num' => $item['cj_review_untrans_num'],
                        'cj_review_trans_num' => $item['cj_review_trans_num'],
                        'cj_status' => $status_div,
                        'xchat_dynamic_id' => !empty($item['xchat_dynamic_id']) ? $item['xchat_dynamic_id'] : '无',
                        'xchat_y_level' => $item['xchat_y_level'],
                        'cj_create_time' => $item['cj_create_time'],
                        'trans_time' => !empty($item['trans_time']) ? date("Y-m-d H:i",$item['trans_time']) : '无',
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
        $status = PostAcquisitionService::statusList();
        $this->assign('cj_status',$status);
        return $this->fetch();
    }

}
