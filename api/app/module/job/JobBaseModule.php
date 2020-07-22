<?php
/**
 * 脚本
 */
namespace api\app\module\job;

class JobBaseModule
{
    public static $errCode = null;
    public static $errMessage = null;

    public static function exceptionError($message, $code = -1)
    {
        self::$errMessage = $message;
        self::$errCode = $code;
    }
}