<?php
namespace app\admin\model;

use think\Db;
use think\Exception;
use think\Model;

class UserModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //模型使用的表
    protected $table = 'user';

    /**
     * 获取用户信息
     * @param $where string|array 条件
     * @param $field string 获取字段
     * @return array
     * @throws
     * @author zjy
     */
    public function userInfo($where,$field = '*')
    {
        $sql = Db::name($this->table);
        if(!empty($where))
        {
            if (is_numeric($where) && (floor($where) - $where) == 0)
            {
                $sql->where("id = {$where}");
            }
            else
            {
                $sql->where($where);
            }
        }

        $result = $sql->field($field)->find();

        return $result;
    }

    /**
     * 查询数据-all
     * @param $where array|string 查询条件（数组或字符串）
     * @param $keywords array 关键词(['name|admin','like','value'])
     * @param $field string 查询字段[0=>主表别名,1=>连表名,2=>连表条件]
     * @param $join array 连表条件
     * @param $order string 排序条件
     * @param $listRow integer 分页显示数量
     * @return bool|object 返回数组
     * @author zjy
     * @throws
     */
    public function selectAll($keywords,$where,$join,$field = '*',$order = 'id desc', $listRow = 20)
    {
        $sql = Db::name($this->table);
        if (!empty($where) && count($keywords) == 3)
        {
            $sql->where($keywords[0],$keywords[1],$keywords[2]);
        }
        if (!empty($where))
        {
            $sql->where($where);
        }
        if (!empty($join) && count($join) == 3)
        {
            $sql->alias($join[0])->join($join[1],$join[2]);
        }
        $result =   $sql
            ->field($field)
            ->order($order)
            ->paginate($listRow);
        if(empty($result))
        {
            return true;
        }
        else
        {
            return $result;
        }
    }

    /**
     * 获取用户信息-one
     * @param $id int
     * @param $field string
     * @param $type int
     * @throws Exception
     * @return bool|array
     */
    public static function getUserInfo($id,$field = '*',$type = 0)
    {
        if(empty($id) && $id > 0){
            return false;
        }
        try{
            if($type === 0){
                $user = Db::name('user')->where(['id'=>$id])->field($field)->find();
            }else{
                $user = Db::name('user')->where(['id'=>$id])->value($field);
            }

            if($user)
            {
                return $user;
            }
            return [];
        }catch (Exception $e) {
            Log::write(sprintf('%s：系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('系统异常:' . $e->getMessage());
        }

    }

    /**
     * 查询单个数据
     * @param $where array
     * @param $field string
     * @param $type int
     * @throws
     * @return array
     */
    public function selectOne($where, $field, $type = 0)
    {
        $sql = Db::name($this->table)->where($where);
        if($type === 0){
            $result = $sql->field($field)->find();
        }else{
            $result = $sql->value($field);
        }

        return $result;
    }

    /**
     * 更新单个数据
     * @param $where array
     * @param $data array
     * @throws
     * @return bool
     */
    public function UpdateOne($where, $data)
    {
        $result = Db::name($this->table)->where($where)->update($data);
        return $result;
    }

}