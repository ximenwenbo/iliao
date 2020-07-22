<?php
namespace cjdata\cjtaqu;

use think\Db;
use think\Log;
use think\Exception;
/**
 * 采集他趣数据用户类
 */
class User extends Base
{
    /**
     * 创建用户信息
     * @param $accountUuid
     * @return bool
     */
    public function createAccountInfo($accountUuid)
    {
        $result = $this->taquapiGetOtherAccountHomepageInfo($accountUuid);
        if (! $result) {
            return false;
        }

        if ($this->addUpdAccountInfo($result)) {
            return true;
        }

        return false;
    }

    /**
     * 获取他趣的用户数据
     * @param $uuid
     * @return bool
     */
    public function taquapiGetOtherAccountHomepageInfo($uuid)
    {
        try {
            $this->freshHeaderParams();
            $header = $this->headerParam;
            $id = $this->distinctRequestId;
            $apiurl = "https://ubd-cn.jiaoliuqu.com/bbs/v5/Account/getOtherAccountHomepageInfo?&distinctRequestId=$id&account_uuid=$uuid";
            $result = $this->getdata($apiurl, $header);
            $aResult = json_decode($result, true);
            if (empty($aResult) || empty($aResult['info']['data'])) {
                Log::write(sprintf('%s：采集他趣数据，根据uuid获取用户详情失败，用户uuid：%s，返回：%s', __METHOD__, $uuid, var_export($result, true)), 'error');
                return false;
            }
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，根据uuid获取用户详情系统错误，用户uuid：%s，错误：%s', __METHOD__, $uuid, $e->getMessage()), 'error');
            return false;
        }

        return $aResult['info']['data'];
    }

    /**
     * 新增或更新用户数据
     * @param $param
     * @return bool
     */
    public function addUpdAccountInfo($param)
    {
        try {
            // 创建DB连接
            $cjtaquDbConn = $this->connectCjtaqu();

            if (!empty($param['account_detail']['baseaddr'])) {
                $baseaddr = explode(',', $param['account_detail']['baseaddr']);
                $provinceId = $baseaddr[0];
                $provinceName = $cjtaquDbConn->table('t_region')->where(['id'=>$baseaddr[0], 'level'=>1])->value('region_name');
                $cityId = $baseaddr[1];
                $cityName = $cjtaquDbConn->table('t_region')->where(['id'=>$baseaddr[1], 'level'=>2])->value('region_name');
            } elseif (!empty($param['account_detail']['hometown'])) {
                $hometown = explode(',', $param['account_detail']['hometown']);
                $provinceId = $hometown[0];
                $provinceName = $cjtaquDbConn->table('t_region')->where(['id'=>$hometown[0], 'level'=>1])->value('region_name');
                $cityId = $hometown[1];
                $cityName = $cjtaquDbConn->table('t_region')->where(['id'=>$hometown[1], 'level'=>2])->value('region_name');
            }
            if (!empty($param['account_detail']['birth'])) {
                $age = $this->getAge4birthday($param['account_detail']['birth']);
            }

            $userFind = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_info']['account_uuid'])->find();

            if ($userFind) {
                // 存在，更新
                !empty($param['account_info']['nickname']) && $updUser['nickname'] = $param['account_info']['nickname'];
                !empty($param['account_info']['avatar']) && $updUser['avatar'] = $param['account_info']['avatar'];
                !empty($param['account_info']['img_list']) && $updUser['img_list'] = json_encode($param['account_info']['img_list']);
                !empty($param['account_detail']['sex_type']) && $updUser['sex_type'] = $param['account_detail']['sex_type'];
                !empty($param['account_detail']['birth']) && $updUser['birth'] = $param['account_detail']['birth'];
                !empty($param['city']) && $updUser['city'] = $param['city'];
                !empty($param['audio_intro']) && $updUser['audio_intro'] = $param['audio_intro'];
                !empty($param['video_intro']) && $updUser['video_intro'] = $param['video_intro'];
                $updUser['age'] = !empty($age) ? $age : 0;
                $updUser['province'] = !empty($provinceName) ? $provinceName : '';
                $updUser['province_id'] = !empty($provinceId) ? $provinceId : '';
                $updUser['city'] = !empty($cityName) ? $cityName : '';
                $updUser['city_id'] = !empty($cityId) ? $cityId : '';
                $updUser['cj_update_time'] = date('Y-m-d H:i:s'); // 采集更新时间

                $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->where('account_uuid', $param['account_info']['account_uuid'])->update($updUser);
                if (! $result) {
                    Log::write(sprintf('%s：采集他趣数据，用户详情入库失败，updUser数据：%s', __METHOD__, var_export($updUser, true)), 'error');
                    return false;
                }
            } else {
                // 不存在，新增
                $addUser = [
                    'account_uuid' => $param['account_info']['account_uuid'],
                    'nickname' => !empty($param['account_info']['nickname']) ? $param['account_info']['nickname'] : '',
                    'avatar' => !empty($param['account_info']['avatar']) ? $param['account_info']['avatar'] : '',
                    'img_list' => !empty($param['account_info']['img_list']) ? json_encode($param['account_info']['img_list']) : '',
                    'sex_type' => !empty($param['account_detail']['sex_type']) ? $param['account_detail']['sex_type'] : 0,
                    'birth' => !empty($param['account_detail']['birth']) ? $param['account_detail']['birth'] : 0,
                    'age' => !empty($age) ? $age : 0,
                    'affectivestatus' => !empty($param['account_detail']['affectivestatus']) ? json_encode($param['account_detail']['affectivestatus']) : 0,
                    'province' => !empty($provinceName) ? $provinceName : '',
                    'province_id' => !empty($provinceId) ? $provinceId : '',
                    'city' => !empty($cityName) ? $cityName : '',
                    'city_id' => !empty($cityId) ? $cityId : '',
                ];

                $result = $cjtaquDbConn->table('t_jiaoliuqu_account')->insert($addUser);
                if (! $result) {
                    Log::write(sprintf('%s：采集他趣数据，用户详情入库失败，addUser数据：%s', __METHOD__, var_export($addUser, true)), 'error');
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            Log::write(sprintf('%s：采集他趣数据，用户详情入库系统错误，param数据：%s，错误：%s', __METHOD__, var_export($param, true), $e->getMessage()), 'error');
            return false;
        }
    }

    /**
     * 根据出生日期获取年龄
     * @param $birthday
     * @return bool|int
     */
    public function getAge4birthday($birthday)
    {
        $age = $birthday;
        if($age === false){
            return false;
        }
        list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
        $now = strtotime("now");
        list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
        $age = $y2 - $y1;
        if((int)($m2.$d2) < (int)($m1.$d1))
            $age -= 1;
        return $age;
    }
}