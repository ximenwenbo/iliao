<?php
namespace app\admin\model;

use think\Exception;
use think\Model;

class TopicCircleModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    //开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * @param $table
     * @param $where
     * @param $order string
     * @param $offset
     * @param $listRow
     * @return array|object
     * @throws Exception
     */
    public static function TableSelect($table,$where,$order,$offset,$listRow){
        $taquDB =  \app\admin\service\forum\CjDataService::connectCjtaqu();
        $sql = $taquDB->name($table);
        if(!empty($where)){
            $sql->where($where);
        }
        if(!empty($order)){
            $sql->order($order);
        }
        if(is_numeric($offset) && is_numeric($listRow) > 0){
            $sql->limit($offset,$listRow);
        }
        $data =  $sql->select();
        return $data;
    }

    /**
     * @param $table
     * @param $field
     * @param $where
     * @return array
     * @throws Exception
     */
    public static function TableFind($table,$where,$field = '*'){
        $taquDB =  \app\admin\service\forum\CjDataService::connectCjtaqu();
        $sql = $taquDB->name($table);
        if(empty($where)){
            return [];
        }
        $data =  $sql->where($where)->field($field)->find();
        return $data;
    }

    /**
     * 查询数据-all
     * @param $where array|string 查询条件（数组或字符串）
     * @param $field string 查询字段[0=>主表别名,1=>连表名,2=>连表条件]
     * @param $join array 连表条件
     * @param $order string 排序条件
     * @param $offset integer 开始处
     * @param $listRow integer 显示数量
     * @param $group string 显示数量
     * @return bool|array 返回数组
     * @author zjy
     * @throws Exception
     */
    public function selectAll($where, $join, $field, $order,$offset, $listRow,$group = '')
    {
        if(empty($where) || empty($join))
        {
            return false;
        }
        $taquDB = \app\admin\service\forum\CjDataService::connectCjtaqu();
        $sql = $taquDB->name('t_jiaoliuqu_post')
                    ->field($field)
                    ->order($order)
                    ->where($where)
                    ->alias($join[0])
                    ->join($join[1],$join[2]);

        $total_sql = clone $sql;
        if(!empty($group)){
            $sql->group($group);
            $total_sql->group($group);
        }
        $total = $total_sql->count();
        $data = $sql->limit($offset,$listRow)->select();

        $result = [
            'total' => $total,
            'data' => $data,
        ];
        if (empty($result)) {
            return true;
        } else {
            return $result;
        }
    }
}