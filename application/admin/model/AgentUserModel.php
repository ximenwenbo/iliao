<?php
/*
 * 菜单
 */
namespace app\admin\model;

use think\Db;
use think\Model;


class AgentUserModel extends Model
{
    //表名
    protected $table = 'agent_user';

    /**
     * 查询数据-all
     * @param $where array|string 查询条件（数组或字符串）
     * @param $join array 连表条件[0=>主表别名,1=>连表名,2=>连表条件]
     * @param $field string 查询字段
     * @param $order string 排序条件
     * @param $limit array 偏移量
     * @return bool|array 返回数组
     * @author zjy
     * @throws
     */
    public function selectAll($where, $join, $field, $order, $limit)
    {
        $sql = Db::name($this->table);
        if(count($join) > 0){
            $sql->alias('a');
            $sql->join($join[0],$join[1]);
        }
        if(!empty($where)){
            $sql->where($where);
        }
        if(!empty($field)){
            $sql->field($field);
        }
        if(!empty($order)){
            $sql->order($order);
        }
        if(!empty($limit)){
            $sql->limit($limit['offset'],$limit['pageSize']);
        }
        //分页数
        $total_sql = clone $sql;
        $total = $total_sql->count();
        //返回数组
        $data = $sql->select()->toArray();
        $result = [
            'total'=>$total,
            'data'=>$data,
        ];
        return $result;
    }

    /**
     * 单表数据
     * @param $where array 条件
     * @param $filed string 条件
     * @return bool|array
     * @throws
     */
    public function selectData($where,$filed = '*'){
        $sql = Db::name($this->table);
        if(is_array($where)){
            $sql->where($where);
            $data = $sql->field($filed)->select()->toArray();
            return $data;
        }else{
            return false;
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
     * @param $where array|string
     * @param $data array
     * @throws
     * @return bool
     */
    public function UpdateOne($where, $data)
    {
        $result = Db::name($this->table)->where($where)->update($data);
        return $result;
    }

    /**
     * 删除单个数据
     * @param $where array
     * @param $data array
     * @throws
     * @return bool
     */
    public function DeleteOne($where, $data)
    {
        $result = Db::name($this->table)->where($where)->delete($data);
        return $result;
    }

    /**
     * 添加单个数据
     * @param $condition array
     * @return int
     */
    public function InsertOne($condition)
    {
        $insert_id = Db::name($this->table)->insert($condition);
        return $insert_id;
    }

}
