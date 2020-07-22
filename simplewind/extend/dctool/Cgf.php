<?php
namespace dctool;
/**
 * 配置类方法
 */
class Cgf
{
    /**
     * 获取金币昵称
     * @return mixed
     */
    public static function getCoinNickname()
    {
        return config('option.coin_nickname');
    }

    /**
     * 获取金币转化比例 1元=?金币
     * @return mixed
     */
    public static function getCoinRate()
    {
        return config('option.coin_rate');
    }
}