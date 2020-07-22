<?php
namespace api\app\module;

class CommonModule extends BaseModule
{
    /**
     * 转换距离描述
     * @param $distance
     * @return string
     */
    public static function convertDistance($distance)
    {
        if ($distance < 1000) {
            $sRet = '<1km';
        } elseif ($distance > 30000) {
            $sRet = '>30km';
        } else {
            $sRet = sprintf('%.1fkm', $distance / 1000);
        }

        return $sRet;
    }
}