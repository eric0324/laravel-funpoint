<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2019/1/30
 * Time: 下午 4:31
 */

namespace TsaiYiHua\FunPoint;


class FunPoint
{
    public static $useFunPointRoute = true;

    public static $sendForm = null;

    public static function ignoreRoutes()
    {
        static::$useFunPointRoute = false;
        return new static;
    }
}