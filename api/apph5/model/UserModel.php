<?php
// +----------------------------------------------------------------------
// | 文件说明：用户表关联model 
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: wuwu <15093565100@163.com>
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Date: 2017-7-26
// +----------------------------------------------------------------------
namespace api\apph5\model;

use think\Db;
use think\Exception;
use think\Model;

class UserModel extends Model
{

    /**
     * token获取用户信息
     * @param $token string
     * @return array
     * @throws Exception
     */
    public function getUserInfo($token)
    {
        $tokenRow = Db::name("user_token")->field('id,user_id,expire_time')->where('token', $token)->find();
        if (empty($tokenRow)) {
            return ['code'=>20033,'msg'=>'用户不存在'];
        }
        if ($tokenRow['expire_time'] < time()) {
            return ['code'=>20044,'msg'=>'token已过期！'];
        }
        return $tokenRow['user_id'];

    }
}
