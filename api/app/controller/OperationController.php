<?php
/**
 * User: coase
 * Date: 2019-01-10
 */
namespace api\app\controller;

use cmf\controller\RestUserBaseController;
use think\Db;
use \think\Log;
use think\Validate;
use think\Exception;
use api\app\module\MaterialModule;
use api\app\module\aliyun\AliyunOssModule;

/**
 * #####运营操作功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1.搜索运营客服的机器人
 * ``````````````````
 */
class OperationController extends RestUserBaseController
{
    /**
     * 搜索运营客服的机器人
     */
    public function searchCustomRobots()
    {
        try {
            $keyword = trim($this->request->param('keyword', '')); // 搜索关键词，支持用户id或者昵称
            $iPage = $this->request->param('page', 1, 'int'); //当前页
            $iPageSize = 1000;
            $userId = $this->getUserId();

            # 获取列表数据
            $query = Db::name('allot_robot')
                ->alias('r')
                ->join('user u', 'u.id = r.robot_id')
                ->where('r.custom_id', $userId)
                ->where("u.user_type", 3);
            if (! empty($keyword)) {
                $query->where('u.id = "'.$keyword.'" OR u.user_nickname LIKE "%'.$keyword.'%"');
            }
            $result = $query->order('u.be_look_num', 'desc')
                ->field('u.*')
                ->page($iPage, $iPageSize)
                ->select();

            $aRet = [];
            foreach ($result as $row) {
                if ($row['virtual_pos'] == 1) {
                    $taps = ['可模拟定位'];
                    $cityName = '';
                } else {
                    $taps = [$row['city_name']];
                    $cityName = $row['city_name'];
                }
                $aRet[] = [
                    'user_id' => $row['id'],
                    'user_nickname' => $row['user_nickname'],
                    'show_photo' => MaterialModule::getFullUrl($row['avatar']),
                    'virtual_pos' => $row['virtual_pos'], // 是否可模拟定位(0:不是 1:是)
                    'city_name' => $cityName,
                    'taps' => $taps,
                ];
            }

            $this->success("OK", ['list' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

}
