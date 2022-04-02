<?php

namespace Webman\Bootstrap;

use Webman\Bootstrap;
use Workerman\Timer;
use think\facade\Cache;

class ThinkCache implements Bootstrap
{
    public static function start($worker)
    {
        $config = config('redis.cache');
        if (!$config) {
            return;
        }
        Cache::config($config);
        if ($worker) {
            Timer::add(55, function () {
                Cache::get('ping');
            });
        }
    }
}