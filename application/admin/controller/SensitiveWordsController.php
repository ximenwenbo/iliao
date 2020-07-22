<?php
/**
 * 敏感词管理
 * @author zjy
 */
namespace app\admin\controller;

use app\admin\service\SensitiveWordsService;
use app\admin\service\txyun\YuntongxinService;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;


class SensitiveWordsController extends AdminBaseController
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
        $result = SensitiveWordsService::RList($condition);
        //var_dump($result);die;
        //调用列表方法
        $data = [];
        if(!empty($result['data'])){
            foreach ($result['data'] as $val)
            {
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
                $filed =  [
                    'id' => $val['id'],
                    'words' => $val['words'],
                    /*'status' => $status,*/
                    'sort' => $val['sort'],
                    'remark' => $val['remark'],
//                    'create_id' => $val['user_login'],
                    'update_time' => empty($val['update_time']) ? '无':date("Y-m-d H:i",$val['update_time']),
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
     * 添加敏感词
     * @throws
     */
    public function AddInfo(){
        if($this->request->isAjax()){
            $param = $this->request->param();
            //数据验证
            if(!isset($param['words']) || empty($param['words'])){
                return json_encode(['code'=>0, 'msg'=>'敏感词不能为空']);
            }else{
                if(mb_strlen($param['words']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'敏感词不能超过100个字符']);
                }
            }

            if(!isset($param['sort']) || $param['sort'] == ''){
                return json_encode(['code'=>0, 'msg'=>'排序不能为空']);
            }else{
                if(!is_numeric($param['sort']) || $param['sort'] < 0)
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }

            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }
            $condition = [
                'words' => $param['words'],
                'sort' => intval($param['sort']),
                'create_time' => time(),
                'status' => 1,
                'remark' => $param['remark'],
                'create_id' => Session::get('ADMIN_ID'),
            ];


            //开启事务
            Db::startTrans();
            try{
                $result = SensitiveWordsService::AddData($condition);
                //var_dump($result);die;
                if(!$result){
                    return json_encode(['code'=>0, 'msg'=>'添加数据失败']);
                }
                //调用腾讯云通讯
                $data = YuntongxinService::SensitiveWordsAdd($condition['words']);
                if(!$data){
                    return json_encode(['code'=>0, 'msg'=>'腾讯云数据新增失败']);
                }
                //提交事务
                Db::commit();
                return json_encode(['code'=>200,'data'=>$result, 'msg'=>'添加数据成功']);
            }catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                return json_encode(['code'=>0, 'msg'=>'添加数据失败']);
            }

        }
        return $this->fetch('add');
    }

    /**
     * 敏感词编辑
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
            if(!isset($param['words']) || empty($param['words'])){
                return json_encode(['code'=>0, 'msg'=>'敏感词不能为空']);
            }else{
                if(mb_strlen($param['words']) > 100)
                {
                    return json_encode(['code'=>0, 'msg'=>'敏感词不能超过100个字符']);
                }
            }

            if(!isset($param['sort']) || $param['sort'] == ''){
                return json_encode(['code'=>0, 'msg'=>'排序不能为空']);
            }else{
                if(!is_numeric($param['sort']))
                {
                    return json_encode(['code'=>0, 'msg'=>'排序必须为整数']);
                }
            }

            if(mb_strlen($param['remark']) > 255)
            {
                return json_encode(['code'=>0, 'msg'=>'备注字符过长']);
            }

            $condition = [
                'words' => $param['words'],
                'sort' => intval($param['sort']),
                'update_time' => time(),
                'remark' => $param['remark'],
            ];
            //开启事务
            Db::startTrans();
            try{
                //原敏感词
                $old_words = SensitiveWordsService::ToInfo(['id'=>$param['id']],'words',-1);
                //数据库修改
                $result = SensitiveWordsService::UpdateB(['id'=>$param['id']],$condition);
                if(!$result){
                    return json_encode(['msg' => '数据修改失败', 'code' => 0]);
                }

                if(!empty($condition['words']))
                {
                    $data = YuntongxinService::SensitiveWordsUpdate($old_words,$condition['words']);
                    if(!$data){
                        return json_encode(['msg' => '腾讯云数据修改失败', 'code' => 0]);
                    }
                }else{
                    return json_encode(['msg' => '敏感词不存在', 'code' => 0]);
                }

                //提交事务
                Db::commit();
                return json_encode(['msg'=> '修改成功', 'code'=>200]);
            }catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                return json_encode(['msg' => $e->getMessage(), 'code' => 0]);
            }

        }
        $id = $this->request->param('id');
        $info = SensitiveWordsService::ToInfo($id);
        if(empty($info)){
            return $this->error("该数据不存在或已过期");
        }
        $this->assign('info',$info);
        return $this->fetch();
    }


    /**
     * 删除数据
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

        //开启事务
        Db::startTrans();
        try{
            $result = SensitiveWordsService::UpdateB(['id'=>$id],$condition);
            if(!$result){
                return json_encode(['msg' => '数据修改失败', 'code' => 0]);
            }

            $words = SensitiveWordsService::ToInfo($id,'words',-1);

            if(!empty($words))
            {
                $data = YuntongxinService::SensitiveWordsDel($words);
                if(!$data){
                    return json_encode(['msg' => '腾讯云数据删除失败', 'code' => 0]);
                }
            }else{
                return json_encode(['msg' => '敏感词不存在', 'code' => 0]);
            }

            //提交事务
            Db::commit();
            return json_encode(['msg'=> '修改成功', 'code'=>200]);
        }catch (Exception $e) {
            // 回滚事务
            Db::rollback();
            return json_encode(['msg' => $e->getMessage(), 'code' => 0]);
        }

    }

    /**
     * 已经存在的敏感词
     */
    public function existList()
    {
        YuntongxinService::SensitiveWordsGet();
    }

}
