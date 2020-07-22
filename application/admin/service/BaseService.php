<?php
/**
 * 基础类，其它 xxService 继承该类
 */
namespace app\admin\service;

class BaseService
{
    public static $errCode = null;
    public static $errMessage = null;

    public static function exceptionError($message, $code = -1)
    {
        self::$errMessage = $message;
        self::$errCode = $code;
    }
}