<?php
namespace app\admin\service;

use app\admin\model\UserModel;
use think\Exception;
use think\Log;

class UserService extends BaseService
{
    /**
     * 列表方法
     * @param $filter array 接收参数
     * @return array
     * @throws \think\exception\DbException
     * @author zjy
     */
    public function CurrentList($filter)
    {
        //参数处理
        $where = [];
        $keywords = [];

        $where['user_type'] = 2;
        if (isset($filter['status']) && $filter['status'] >= 0 && $filter['status'] != '')
        {
            $where['user_status'] = $filter['status'];
        }

        if (isset($filter['keyword']) && !empty($filter['keyword']))
        {
            $keywords = ['user_nickname|id' ,'like', "%{$filter['keyword']}%"];
        }

        //返回字段
        $field = '';
        //连表
        $join = [];

        //调用模型 处理
        $model = new UserModel();
        $result = $model->selectAll($keywords,$where,$join,$field);
        if($result === false)
        {
            return ['code' => 0, 'msg' => '程序错误'];
        }
        elseif ($result === true)
        {
            return ['code' => 1, 'data'=>[],'page' => ''];
        }
        else
        {
            $result->appends($filter);
            return [
                'code' => 200,
                'data' => $result->items(),
                'page' => $result->render(),
            ];
        }
    }

    /**
     * 个人详情
     * @author zjy
     * @throws
     */
    public function UserDetails($id)
    {
        if(empty($id))
        {
            return false;
        }
        $model = new UserModel();
        $info = $model->userInfo($id);
        if($info)
        {
            return $info;
        }else{
            return false;
        }
    }


    /**
     * 状态配置
     * @return array
     */
    public function statusListSelect()
    {
        $aRet = [
            0 => '禁用',
            1 => '正常',
        ];
        return $aRet;
    }


    /**
     * 单数据查询
     * @param $where array|int|string
     * @param $field string
     * @param $type int
     * @throws Exception
     * @return array|string
     */
    public static function ToInfo($where,$field = '*',$type = 0)
    {
        try{
            if(is_numeric($where)){
                $where = ['id'=>$where];
            }
            //调用模型 处理
            $model = new UserModel();
            if(empty($type)){
                $data = $model->selectOne($where,$field);
            }else{
                $data = $model->selectOne($where,$field,1);
            }

            if($data)
            {
                return $data;
            }else{
                return '';
            }
        }catch (Exception $exception)
        {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * 更新数据
     * @param $where array
     * @param $condition array
     * @throws Exception
     * @return int
     */
    public static function UpdateData($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new UserModel();
            $res = $model->UpdateOne($where,$condition);
            if($res){
                return $res;
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception($exception->getMessage());
        }

    }

}