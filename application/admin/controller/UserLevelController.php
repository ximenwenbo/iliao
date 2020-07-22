<?php
/**
 * 等级配置
 * @author coase
 */
namespace app\admin\controller;

use app\admin\service\UserLevelService;
use cmf\controller\AdminBaseController;
use think\Validate;
use think\Db;


class UserLevelController extends AdminBaseController
{
    /**
     * 列表
     * @throws
     */
    public function IndexList()
    {
        //table表单请求数据
        if($this->request->isAjax()){
            $params = $this->request->param();
            $condition = [
                'keywords' => isset($params['data']['keywords']) ? $params['data']['keywords'] : '',
                'start_time' => isset($params['data']['startDate']) ? strtotime($params['data']['startDate']) : '',
                'end_time' => isset($params['data']['endDate']) && !empty($params['data']['endDate']) ? strtotime($params['data']['endDate'])+86399 : '',
                'pageSize' => isset($params['pageSize']) ? $params['pageSize'] : '',
                'sortField' =>  isset($params['sortField']) ? $params['sortField'] : '',
                'sortType' => isset($params['sortType']) ? $params['sortType'] : '',
                'offset' => isset($params['pageIndex']) ? $params['pageIndex'] : '',
            ];

            //调用列表方法
            $result = UserLevelService::RList($condition);
            $data = [];
            if(!empty($result['data'])){
                foreach ($result['data'] as $value)
                {
                    $opera = ' 
                        <button type="button" class="btn btn-outline btn-info btn-sm" onclick="editPopup('.$value['id'].')">
                            <i class="icon wb-edit" aria-hidden="true" ></i> 编辑
                        </button>
                        <button type="button" class="btn btn-outline btn-danger btn-sm" onclick="delPopup('.$value['id'].')">
                            <i class="icon wb-trash" aria-hidden="true"></i> 删除
                        </button>
                        ';

                    $filed =  [
                        'level_id' => $value['level_id'],
                        'level_name' => $value['level_name'],
                        'level_point' => $value['level_point'],
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
                'level_id' => 'require|number|between:1,100',
                'level_name' => 'require',
                'level_point' => 'require|number',
            ]);
            $validate->message([
                'level_id.require' => '等级不能为空',
                'level_id.between' => '等级必须在1-100之间',
                'level_name.require' => '等级名称不能为空',
                'level_point.require' => '积分值不能为空',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }

            if (Db::name('user_level_setting')->where('level_id', $params['level_id'])->count()) {
                return json_encode(['msg' => '该等级已经存在', 'code'=> 0]);
            }
            if (Db::name('user_level_setting')->where('level_name', $params['level_name'])->count()) {
                return json_encode(['msg' => '该等级名称已经存在', 'code'=> 0]);
            }
            if (Db::name('user_level_setting')->where('level_point', $params['level_point'])->count()) {
                return json_encode(['msg' => '该积分值已经存在', 'code'=> 0]);
            }

            // 该积分值必须大于上一级积分值
            $preLevel = Db::name('user_level_setting')
                ->where('level_id', '<', $params['level_id'])
                ->order('level_id', 'desc')
                ->find();
            if ($preLevel && $preLevel['level_point'] >= $params['level_point']) {
                return json_encode(['msg' => '该积分值必须大于上一级积分值:' . $preLevel['level_point'], 'code'=> 0]);
            }

            // 该积分值必须小于下一级积分值
            $aftLevel = Db::name('user_level_setting')
                ->where('level_id', '>', $params['level_id'])
                ->order('level_id', 'asc')
                ->find();
            if ($aftLevel && $aftLevel['level_point'] <= $params['level_point']) {
                return json_encode(['msg' => '该积分值必须小于下一级积分值:' . $aftLevel['level_point'], 'code'=> 0]);
            }

            $condition = [
                'level_id' => $params['level_id'],
                'level_name' => $params['level_name'],
                'level_point' => $params['level_point'],
            ];
            $res = UserLevelService::AddData($condition);
            if($res){
                // 更新缓存
                UserLevelService::freshLevelCache();

                return json_encode(['code' => 200, 'msg' => '添加成功']);
            }else{
                return json_encode(['msg' => '添加失败', 'code'=> 0]);
            }
        }

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
            ]);
            $validate->message([
                'id.require' => 'id不能为空',
            ]);
            if (!$validate->check($params)) {
                return json_encode(['msg' => $validate->getError(), 'code'=> 0]);
            }

            $condition = [
                'level_id' => $params['level_id'],
                'level_name' => $params['level_name'],
                'level_point' => $params['level_point'],
            ];

            $res = UserLevelService::UpdateB(['id'=>$params['id']],$condition);
            if($res){
                // 更新缓存
                UserLevelService::freshLevelCache();

                return json_encode(['code' => 200, 'msg' => '保存成功']);
            }else{
                return json_encode(['code' => 0, 'msg' => '保存失败']);
            }
        }
        $id = $this->request->param('id',0);
        if(empty($id)){
            return $this->error('参数有误');
        }
        $info = UserLevelService::ToInfo(['id'=>$id]);
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
        $result = UserLevelService::DeleteB(['id'=>$param['id']]);

        if(empty($result))
        {
            return json_encode(["code"=>0, "msg"=>"删除失败",]);
        }
        else
        {
            // 更新缓存
            UserLevelService::freshLevelCache();

            return json_encode(["code"=>200, "msg"=>"删除成功!",]);
        }
    }
}
