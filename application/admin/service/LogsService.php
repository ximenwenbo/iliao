<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\service;

use think\Db;
use think\Exception;
use think\Log;
use think\Request;
use think\response\Json;

class LogsService
{
    /**
     * 添加操作记录
     * @param $type int 操作类型 1：登录 2登出 3修改密码 4修改会员信息
     * @param $url string 操作路由
     * @param $remark string 备注说明
     * @param $params string|Json 路由参数
     * @throws Exception
     * @return bool
     * @author zjy
     */
    public static function addRecord($type = 0, $url='', $remark = '', $params = '' )
    {
        try{
            $admin_id = cmf_get_current_admin_id();
            $admin_ip = Request::instance()->ip();
            $condition = [
                'type' => $type,
                'url' => $url,
                'params' => $params,
                'create_time' => time(),
                'remark' => $remark,
                'admin_id' => $admin_id,
                'admin_ip' => $admin_ip,
            ];
            $res = Db::name('admin_log_record')->insertGetId($condition);
            if(!empty($res)){
                return true;
            }
            else
            {
                Log::write(sprintf('%s：写入操作日志失败：%s', __METHOD__, var_export($res, true)),'error');
                throw new Exception('写入操作日志失败');
            }
        }catch (Exception $e)
        {
            Log::write(sprintf('%s：写入操作日志系统异常：%s', __METHOD__, $e->getMessage()),'error');
            throw new Exception('写入操作日志系统异常:' . $e->getMessage());
        }
    }


}