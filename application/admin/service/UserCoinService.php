<?php
namespace app\admin\service;

use app\admin\model\UserCoinModel;
use think\Db;
use think\Exception;
use think\Log;

class UserCoinService extends BaseService
{
    /**
     * 充值订单列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function RList($filter)
    {
        //条件处理
        $where = 'a.class_id=3 and a.change_type = 2';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.user_id = {$filter['keywords']} or u.mobile like '%{$filter['keywords']}%')  ";
        }

        //搜索提交时间
        if(!empty($filter['start_time']))
        {
            $where .= " and a.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and a.create_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_field = [1=>'id', 2 => 'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.user_id'];
        //返回字段
        $field = 'a.*, u.id user_id, u.user_nickname, u.mobile';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new UserCoinModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

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
            $model = new UserCoinModel();
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
     * 添加数据
     * @param $condition array 条件
     * @throws Exception
     * @return int
     */
    public static function AddData($condition)
    {
        try{
            if(is_array($condition))
            {
                $model = new UserCoinModel();
                $insert_id = $model->InsertOne($condition);
                if($insert_id > 0){
                    return $insert_id;
                }else{
                    Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($insert_id, true)),'error');
                    throw new Exception('新增数据失败');
                }
            }

        }catch (Exception $exception)
        {
            Log::write(sprintf('%s：新增数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('新增数据失败');
        }
    }

    /**
     * 更新数据
     * @param $where array
     * @param $condition array
     * @throws Exception
     * @return int
     */
    public static function UpdateB($where, $condition)
    {
        try{
            if (!is_array($where) || !is_array($condition)){
                throw new Exception('参数错误');
            }
            $model = new UserCoinModel();
            $res = $model->UpdateOne($where,$condition);
            if($res){
                return $res;
            }else{
                throw new Exception('修改数据失败');
            }
        }catch (Exception $exception){
            Log::write(sprintf('%s：修改数据失败：%s', __METHOD__, var_export($condition, true)),'error');
            throw new Exception('修改数据失败');
        }

    }


    /**
     * 状态配置
     * @param $key int
     * @return array|string
     */
    public static function statusList($key=-1){
        $aRet = [
            0 => '未支付',
            1 => '支付中',
            2 => '支付成功',
            10 => '支付失败',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '无';
        }
        return $aRet;
    }

    /**
     * 渠道配置
     * @param $key int
     * @return array|string
     */
    public static function changeList($key=-1){
        $aRet = [
            '1' => '充值',
            '2' => '提现',
            '31' => '音视频聊天',
            '32' => '送礼物',
            '33' => '收费直播间',
            '34' => '守护主播',
            '41' => '音视频聊天收入',
            '42' => '收礼物收入',
            '43' => '收费直播间收入',
            '44' => '获得守护收入',
        ];
        if($key>=0){
            return isset($aRet[$key]) ? $aRet[$key] : '无';
        }
        return $aRet;
    }


    /**
     * 充值订单列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function URList($filter)
    {
        //条件处理
        $where = 'a.status=2';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.user_id like '%{$filter['keywords']}%' or u.mobile like '%{$filter['keywords']}%')  ";
        }

        //搜索时间
        if(!empty($filter['start_time']))
        {
            $where .= " and a.create_time >= {$filter['start_time']}";
        }
        if(!empty($filter['end_time'])){
            $where .= " and a.create_time <= {$filter['end_time']}";
        }

        //排序字段
        $sort_field = [1=>'id', 2 => 'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.user_id'];
        //返回字段
        $field = 'a.*, u.id user_id, u.user_nickname, u.mobile';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new UserCoinModel();
        $result = $model->selectAll($where,$join,$field,$sort,$offset,$pageSize);
        if($result){
            return $result;
        }else{
            return false;
        }

    }

    /**
     * 详情查询
     * @param $change_class_id
     * @param $change_data_id
     * @throws
     * @return array|string
     */
    public static function detailsAbout($change_class_id,$change_data_id){
        switch ($change_class_id){
            case 34:
                $sql = Db::name('watch_order')
                    ->field('s.user_nickname s_nickname,s.avatar s_avatar,s.mobile s_mobile,s.id sid,
                                r.user_nickname r_nickname,r.mobile r_mobile,r.avatar r_avatar,r.id rid,
                                gg.coin,gg.day_time,gg.create_time')
                    ->alias('gg')
                    ->join('user s','s.id=gg.send_uid')
                    ->join('user r','r.id=gg.receive_uid')
                    ->where(['gg.id'=>$change_data_id]);
                $data = $sql->find();
                return $data;
                break;
            case 33:
                $sql = Db::name('live_in_order')
                    ->field('s.user_nickname s_nickname,s.avatar s_avatar,s.mobile s_mobile,s.id sid,
                                r.user_nickname r_nickname,r.mobile r_mobile,r.avatar r_avatar,r.id rid,
                                gg.live_id,gg.live_uid,gg.cost,gg.create_time')
                    ->alias('gg')
                    ->join('user s','s.id=gg.user_id')
                    ->join('user r','r.id=gg.live_uid')
                    ->where(['gg.id'=>$change_data_id]);
                $data = $sql->find();
                return $data;
                break;
            case 32:
                $sql = Db::name('gift_given_order')
                                ->field('
                                            s.user_nickname s_nickname,s.avatar s_avatar,s.mobile s_mobile,s.id sid,
                                            r.user_nickname r_nickname,r.mobile r_mobile,r.avatar r_avatar,r.id rid,
                                            g.name g_name,g.coin,
                                            gg.total_coin,gg.num,gg.order_no,gg.send_time,gg.receive_time
                                        ')
                                ->alias('gg')
                                ->join('user s','s.id=gg.send_uid')
                                ->join('user r','r.id=gg.receive_uid')
                                ->join('gift g','g.uni_code=gg.gift_uni_code')
                                ->where(['gg.id'=>$change_data_id]);
                    $data = $sql->find();

                    //return $sql->getLastSql();
                    return $data;
                break;
            case 31:
                $sql = Db::name('chat_order')
                    ->field('
                                s.user_nickname s_nickname,s.avatar s_avatar,s.mobile s_mobile,s.id sid,
                                r.user_nickname r_nickname,r.mobile r_mobile,r.avatar r_avatar,r.id rid,
                                gg.home_id,gg.order_no,gg.type,gg.duration,gg.per_cost,gg.cost,gg.status,
                                gg.finish_time,gg.create_time
                            ')
                    ->alias('gg')
                    ->join('user s','s.id=gg.launch_uid')
                    ->join('user r','r.id=gg.accept_uid')
                    ->where(['gg.id'=>$change_data_id]);
                $data = $sql->find();

                //return $sql->getLastSql();
                return $data;
                break;
            default:
                return [];
                break;
        }
    }

}