<?php
namespace dctxyun;

class Base
{
    public static $errCode = null;
    public static $errMessage = null;

    public static function exceptionError($message, $code = 0)
    {
        self::$errMessage = $message;
        self::$errCode = $code;
    }
}