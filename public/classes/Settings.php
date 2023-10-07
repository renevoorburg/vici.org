<?php

/**
 * Created by PhpStorm.
 * User: rene
 * Date: 15-01-17
 * Time: 11:24
 */

// from http://stackoverflow.com/questions/3724584/what-is-the-best-way-to-save-config-variables-in-a-php-web-app

class Settings
{
    static private $protected = array(); // For DB / passwords etc
    static private $public = array(); // For all public strings such as meta stuff for site

    public static function getProtected($key)
    {
        return isset(self::$protected[$key]) ? self::$protected[$key] : false;
    }

    public static function getPublic($key)
    {
        return isset(self::$public[$key]) ? self::$public[$key] : false;
    }

    public static function setProtected($key,$value)
    {
        self::$protected[$key] = $value;
    }

    public static function setPublic($key,$value)
    {
        self::$public[$key] = $value;
    }

    public function __get($key)
    {//$this->key // returns public->key
        return isset(self::$public[$key]) ? self::$public[$key] : false;
    }

    public function __isset($key)
    {
        return isset(self::$public[$key]);
    }
}