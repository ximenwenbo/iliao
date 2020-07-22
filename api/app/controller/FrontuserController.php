<?php
/**
 * User: coase
 * Date: 2018-10-18
 * Time: 17:50
 */
namespace api\app\controller;

use api\app\module\rongcloud\RongCloudModule;
use cmf\controller\RestBaseController;
use think\Db;
use think\Log;
use think\Validate;
use think\Exception;
use api\app\module\VipModule;
use api\app\module\UserModule;
use api\app\module\RoleModule;
use api\app\module\MaterialModule;
use api\app\module\aliyun\AliyunOssModule;

/**
 * #####用户操作自己的功能模块 提供接口列表如下（HTTP方式调用）：
 * ``````````````````
 * 1. 根据用户id获取用户基本信息
 * 2. 根据token获取用户信息
 * ``````````````````
 */
class FrontuserController extends RestBaseController
{
    /**
     * 根据用户id获取用户基本信息
     */
    public function getUserinfoByUid()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer'
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId   = $param['user_id'];
            $fieldStr = '*';

            $userInfo = Db::name("user")->field($fieldStr)->find($userId);
            if (!$userInfo) {
                $this->error('获取失败!');
            }

            $aRet = [
                'user_id' => $userInfo['id'],
                'user_nickname' => $userInfo['user_nickname'],
                'avatar' => MaterialModule::getFullUrl($userInfo['avatar']),
            ];

            $this->success("OK", ['user_info' => $aRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 根据token获取用户信息
     */
    public function getUserInfoByToken()
    {
        try {
            $validate = new Validate([
                'token' => 'require'
            ]);

            $validate->message([
                'token.require' => '请输入用户token!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            // token校验
            $tokenRow = Db::name("user_token")->where('token', $param['token'])->find();
            if (empty($tokenRow)) {
                $this->error('您查询的用户不存在！');
            }
            if ($tokenRow['expire_time'] < time()) {
                $this->error('token已过期！');
            }

            // 获取用户数据
            $userId = $tokenRow['user_id'];
            $userData = Db::name("user")->find($userId);

            $userTokenRow = Db::name("user_token")->where('user_id', $userId)->find();
            $userSettingRow = Db::name("user_setting")->where('user_id', $userId)->find();
            // 获取我关注的人数
            $followNum = Db::name("user_follow")->where(['user_id'=>$userId, 'status'=>1])->count();

            # 拼装返回值
            $userRet = [
                'user_id' => $userData['id'],
                'token' => isset($userTokenRow['token']) ? $userTokenRow['token'] : '',
                'user_nickname' => $userData['user_nickname'],
                'sex' => $userData['sex'],
                'age' => $userData['age'],
                'mobile' => $userData['mobile'],
                'qq' => $userData['qq'],
                'weixin' => $userData['weixin'],
                'avatar' => MaterialModule::getFullUrl($userData['avatar']), // 头像
                'signature' => htmlspecialchars_decode($userData['signature']),
                'speech_introduction' => MaterialModule::getFullUrl($userData['speech_introduction']), // 语音介绍
                'album' => UserModule::formatAlbumFullUrl(htmlspecialchars_decode($userData['album'])),
                'video' => UserModule::formatVideoFullUrl(htmlspecialchars_decode($userData['video'])),
                'tags' => $userData['tags'],
                'is_vip' => VipModule::checkIsVip($userData['vip_expire_time']), // 是否vip (1:是 0:否)
                'be_look_num' => $userData['be_look_num'], // 看过我
                'be_follow_num' => $userData['be_follow_num'], // 关注我
                'follow_num' => $followNum, // 我关注的
                'province_name' => $userData['province_name'],
                'city_name' => $userData['city_name'],
                'district_name' => $userData['district_name'],
                'open_video' => isset($userSettingRow['open_video']) ? $userSettingRow['open_video'] : 0,
                'video_cost' => isset($userSettingRow['video_cost']) ? $userSettingRow['video_cost'] : 0,
                'open_speech' => isset($userSettingRow['open_speech']) ? $userSettingRow['open_speech'] : 0,
                'speech_cost' => isset($userSettingRow['speech_cost']) ? $userSettingRow['speech_cost'] : 0,
            ];

            $this->success('OK', ['user_info' => $userRet]);

        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 根据用户id获取用户vip和角色信息
     */
    public function getUserVipRoleByUid()
    {
        try {
            $validate = new Validate([
                'user_id' => 'require|integer'
            ]);

            $validate->message([
                'user_id.require' => '请输入用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $userId   = $param['user_id'];
            $fieldStr = '*';

            $userInfo = Db::name("user")->field($fieldStr)->find($userId);
            if (!$userInfo) {
                $this->error('获取失败!');
            }

            if (RoleModule::checkIsCompanyPromotionAnchorByUid($userId)) {
                $is_company_anchor = 1;
            } else {
                $is_company_anchor = 0;
            }

            $aRet = [
                'user_id'           => $userInfo['id'],
                'is_vip'            => VipModule::checkIsVip($userInfo['vip_expire_time']), // 是否vip (1:是 0:否)
                'vip_expire_time'   => date('Y-m-d', $userInfo['vip_expire_time']),
                'is_company_anchor' => $is_company_anchor,
            ];

            $this->success("OK", $aRet);
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * 根据用户id批量获取用户vip
     */
    public function getUsersVip()
    {
        try {
            $validate = new Validate([
                'user_ids' => 'require' // 用户id,多个用英文逗号分割
            ]);

            $validate->message([
                'user_ids.require' => '请输入用户id!'
            ]);

            $param = $this->request->param();
            if (!$validate->check($param)) {
                $this->error($validate->getError());
            }

            $aUserId = explode(',', trim($param['user_ids'], ','));

            $userResult = Db::name("user")->whereIn('id', $aUserId)->field('id,vip_expire_time')->select();
            if (!$userResult) {
                $this->success('数据为空');
            }

            $aRet = [];
            foreach ($userResult as $item) {
                $aRet[] = [
                    'user_id' => $item['id'], // 用户id
                    'is_vip' => VipModule::checkIsVip($item['vip_expire_time'])  // 是否vip (1:是 0:否)
                ];
            }

            $this->success("OK", ['list' => $aRet]);
        } catch (Exception $e) {
            Log::write(sprintf('%s：系统错误: %s IN: %s LINE: %s', __METHOD__, $e->getMessage(), $e->getFile(), $e->getLine()),'error');

            $this->error(['code' => 9999, 'msg' => $e->getMessage()]);
        }
    }
}
