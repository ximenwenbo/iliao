<?php
namespace app\admin\service;

use app\admin\model\WithdrawOrderModel;
use think\Exception;
use think\Session;
use think\Log;
use think\Db;

class WithdrawOrderService extends BaseService
{
    /**
     * 礼物列表
     * @param $filter array 接收参数
     * @return array|int
     * @throws
     * @author zjy
     */
    public static function RList($filter)
    {
        //条件处理
        $where = 'u.user_type=2';
        //关键词
        if (!empty($filter['keywords']))
        {
            $where .= " and (a.order_no like '%{$filter['keywords']}%' or a.withdraw_account like '%{$filter['keywords']}%') ";
        }
        //提现类型
        if (isset($filter['type']) && is_numeric($filter['type']))
        {
            $where .= " and a.type = {$filter['type']}";
        }else{
            $where .= " and a.type >= 0";
        }
        //状态
        if (isset($filter['status']) && is_numeric($filter['status']))
        {
            $where .= " and a.status = {$filter['status']}";
        }else{
            $where .= " and a.status >= 0";
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
        $sort_field = [1=>'id', 4 => 'amount', 5=>'withdraw_account', 3 => 'user_id'];
        if(isset($sort_field[$filter['sortField']]) && !empty($filter['sortType']))
        {
            $sort = "a.{$sort_field[$filter['sortField']]} {$filter['sortType']}";
        }else{
            $sort = "a.id desc";
        }

        //连表
        $join = ['a','user u', 'u.id=a.user_id'];
        //返回字段
        $field = 'a.*, u.user_login, u.id user_id, u.user_nickname, u.mobile';

        //limit
        $offset = !empty($filter['offset']) ? $filter['offset'] : 0;

        //page
        $pageSize = !empty($filter['pageSize']) ? $filter['pageSize'] : 10;

        //调用模型 处理
        $model = new WithdrawOrderModel();
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
            $model = new WithdrawOrderModel();
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
                $model = new WithdrawOrderModel();
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
            $model = new WithdrawOrderModel();
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
     * 提现类型
     * @param $key int
     * @return array
     */
    public static function type($key=-1){
        $aRet = [
            1 => '可提现余额',
            2 => '推广奖励金',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }

    /**
     * 类型配置
     * @param $key int
     * @return array
     */
    public static function typeList($key=-1){
        $aRet = [
            1 => '审批中',
            2 => '审批通过打款中',
            3 => '已打款',
            10 => '审批未通过',
        ];
        if($key>=0){
            return $aRet[$key];
        }
        return $aRet;
    }

    /**
     * 通过提现审核
     * @param int $withdrawId
     * @return bool
     */
    public static function approvalSuccess4withdraw($withdrawId)
    {
        $time = time();
        $admin_id = Session::get('ADMIN_ID');

        DB::startTrans(); // 开启事务
        try {
            $withdrawFind = Db::name('withdraw_order')->lock(true)->where('id', $withdrawId)->find();
            if (! $withdrawFind) {
                DB::rollback();
                self::exceptionError(sprintf('没有找到该提现订单数据: $withdrawId=%s', $withdrawId), -9010);
                return false;
            }
            $coin = $withdrawFind['coin'];

            $userRow = Db::name('user')->lock(true)->where('id', $withdrawFind['user_id'])->find();

            if ($withdrawFind['type'] == 1) {
                if ($userRow['withdraw_frozen_coin'] < $coin) {
                    DB::rollback();
                    self::exceptionError('冻结余额不足', -9011);
                    return false;
                }

                //更新用户可提现余额
                $updUser = [
                    'withdraw_frozen_coin' => Db::raw('withdraw_frozen_coin-' . $coin),
                    'withdraw_used_coin' => Db::raw('withdraw_used_coin+' . $coin),
                ];
                Db::name('user')->where('id', $userRow['id'])->update($updUser);

                // 新增用户提现金币变更记录
                $insertAcceptCoin = [
                    'user_id' => $withdrawFind['user_id'],
                    'change_type' => 2, // 变动方向 1增加 2减少
                    'coin_type' => 1, // 1:可提现 2:不可提现
                    'class_id' => 2, // 类别 1:充值,2:提现 3:支付 4:收入
                    'change_class_id' => 2, // 2:提现
                    'change_coin' => $withdrawFind['coin'],
                    'coin' => Db::name('user')->where('id', $withdrawFind['user_id'])->value('withdraw_coin'),
                    'change_data_id' => $withdrawFind['id'],
                    'change_subject' => '金币提现',
                    'create_time' => $time
                ];
                Db::name('user_coin_record')->insert($insertAcceptCoin);

            } else {
                if ($userRow['bonus_frozen_coin'] < $coin) {
                    DB::rollback();
                    self::exceptionError('冻结余额不足', -9011);
                    return false;
                }

                //更新用户推广奖励余额
                $updUser = [
                    'bonus_frozen_coin' => Db::raw('bonus_frozen_coin-' . $coin),
                    'bonus_used_coin' => Db::raw('bonus_used_coin+' . $coin),
                ];
                Db::name('user')->where('id', $userRow['id'])->update($updUser);
            }

            //更新提现订单
            $updWithdraw = [
                'status' => 2, // 状态：0:默认 1:审批中 2:审批通过打款中 3:已打款完成 10:审批拒绝
                'audit_time' => $time,
                'update_time' => $time,
                'confirm_user' => $admin_id,
            ];
            Db::name('withdraw_order')->where('id', $withdrawId)->update($updWithdraw);

            Db::commit();
            return true;

        } catch (Exception $e) {
            self::exceptionError(sprintf('确认提现，系统错误: errmsg %s, errcode %s', $e->getMessage(), $e->getCode()), -9999);

            Db::rollback();
            return false;
        }
    }

    /**
     * 拒绝提现审核
     * @param int $withdrawId
     * @param string $errMsg
     * @return bool
     */
    public static function approvalFail4withdraw($withdrawId, $errMsg)
    {
        $time = time();
        $admin_id = Session::get('ADMIN_ID');

        Db::startTrans();//开启事务
        try {
            $withdrawFind = Db::name('withdraw_order')->lock(true)->where('id', $withdrawId)->find();
            if (! $withdrawFind) {
                Db::rollback();
                self::exceptionError(sprintf('没有找到该提现订单数据: $withdrawId=%s', $withdrawId), -9010);
                return false;
            }
            $coin = $withdrawFind['coin'];

            $userRow = Db::name('user')->lock(true)->where('id', $withdrawFind['user_id'])->find();

            if ($withdrawFind['type'] == 1) {
                if ($userRow['withdraw_frozen_coin'] < $coin) {
                    Db::rollback();
                    self::exceptionError('冻结余额不足', -9011);
                    return false;
                }

                //更新用户余额数据
                $updUser = [
                    'withdraw_frozen_coin' => Db::raw('withdraw_frozen_coin-' . $coin),
                    'withdraw_coin' => Db::raw('withdraw_coin+' . $coin),
                ];
                Db::name('user')->where('id', $withdrawFind['user_id'])->update($updUser);

            } else {
                if ($userRow['bonus_frozen_coin'] < $coin) {
                    DB::rollback();
                    self::exceptionError('冻结余额不足', -9011);
                    return false;
                }

                //更新用户推广奖励余额
                $updUser = [
                    'bonus_frozen_coin' => Db::raw('bonus_frozen_coin-' . $coin),
                    'bonus_coin' => Db::raw('bonus_coin+' . $coin),
                ];
                Db::name('user')->where('id', $userRow['id'])->update($updUser);
            }

            //更新提现订单
            $updWithdraw = [
                'status' => 10, // 状态：0:默认 1:审批中 2:审批通过打款中 3:已打款完成 10:审批拒绝
                'err_msg' => $errMsg,
                'audit_time' => $time,
                'update_time' => $time,
                'confirm_user' => $admin_id,
            ];
            Db::name('withdraw_order')->where('id', $withdrawId)->update($updWithdraw);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            self::exceptionError(sprintf('决绝提现，系统错误: errmsg %s, errcode %s', $e->getMessage(), $e->getCode()), -9999);

            Db::rollback();
            return false;
        }
    }

    /**
     * 确认已打款
     * @param int $withdrawId
     * @return bool
     */
    public static function paymentWithdraw($withdrawId)
    {
        $time = time();
        $admin_id = Session::get('ADMIN_ID');

        Db::startTrans(); // 开启事务
        try {
            $withdrawFind = Db::name('withdraw_order')->lock(true)->where('id', $withdrawId)->find();
            if (! $withdrawFind) {
                Db::rollback();
                self::exceptionError(sprintf('没有找到该提现订单数据: $withdrawFind=%s', $withdrawFind), -9010);
                return false;
            }
            if ($withdrawFind['status'] != 2) {
                Db::rollback();
                self::exceptionError(sprintf('提现订单状态有误: $withdrawFind=%s', $withdrawFind), -9012);
                return false;
            }

            Db::name('withdraw_order')->where('id', $withdrawId)->update([
                'status' => 3, // 状态：0:默认 1:审批中 2:审批通过打款中 3:已打款完成 10:审批拒绝
                'payment_time' => $time,
                'update_time' => $time,
                'confirm_user' => $admin_id,
            ]);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();

            self::exceptionError(sprintf('确认已打款，系统错误: errmsg %s, errcode %s', $e->getMessage(), $e->getCode()), -9999);
            return false;
        }
    }
}