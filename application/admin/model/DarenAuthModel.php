<?php
namespace app\admin\model;

use think\Db;
use think\Model;

class DarenAuthModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    //表名
    protected $table = 'user_daren_auth';

    /**
     * 查询数据-all
     * @param $where array|string 查询条件（数组或字符串）
     * @param $field string 查询字段[0=>主表别名,1=>连表名,2=>连表条件]
     * @param $join array 连表条件
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
            ->where($where)
            ->alias($join[0])
            ->join($join[1],$join[2]);

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


}