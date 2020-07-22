<?php
/**
 * 用户角色功能模块
 */
namespace api\app\module;

use think\Db;

class RoleModule extends BaseModule
{
    /**
     * 判断用户是否是公司运营主播
     *
     * @param $userId
     * @return bool
     */
    public static function checkIsCompanyPromotionAnchorByUid($userId)
    {
        $res = Db::name('role_user')->alias('ru')
            ->join('role r', 'r.id=ru.role_id')
            ->where('ru.user_id', $userId)
            ->where('r.uni_code', 'company_promotion_anchor')
            ->field('r.*')
            ->count();

        if ($res) {
            return true;
        } else {
            return false;
        }
    }

}