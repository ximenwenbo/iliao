<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class UserMemberModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //表名
    protected $table = 'user';

    /**
     * 查询数据-all
     * @param $where array|string 查询条件（数组或字符串）
     * @param $join array 连表条件[0=>主表别名,1=>连表名,2=>连表条件]
     * @param $field string 查询字段
     * @param $order string 排序条件
     * @param $offset integer 开始处
     * @param $listRow integer 显示数量
     * @return bool|array 返回数组
     * @author zjy
     * @throws
     */
    public function selectAll($where, $join, $field, $order, $offset, $listRow)
    {
        $sql = Db::name($this->table)
            ->field($field)
            ->order($order)
            ->where($where);
        if(!empty($join)){
            $sql->alias($join[0])->join($join[1],$join[2]);
        }
        $total_sql = clone $sql;
        $total = $total_sql->count();
        $data = $sql->limit($offset,$listRow)
            ->select()
            ->toArray();
        //return $sql->limit($offset,$listRow)->getLastSql();
        $result = [
            'total'=>$total,
            'data'=>$data,
        ];

        return $result;

    }

    /**
     * 查询单个数据
     * @param $where array
     * @param $field string
     * @param $type int
     * @throws
     * @return array|string
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
        $insert_id = Db::name($this->table)->insertGetId($condition);
        return $insert_id;
    }
}