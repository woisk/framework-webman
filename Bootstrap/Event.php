<?php

namespace Webman\Bootstrap;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Webman\Bootstrap;

/**
 * Class Event
 * @method static \Illuminate\Events\Dispatcher dispatch($event)
 */
class Event implements Bootstrap
{
    /**
     * @var Dispatcher
     */
    protected static $instance = null;


    public static function start($worker)
    {
        if ($worker) {
            $container = new Container;
            static::$instance = new Dispatcher($container);
            $eventsList = config('event');
            if (isset($eventsList['listener']) && !empty($eventsList['listener'])) {
                foreach ($eventsList['listener'] as $event => $listener) {
                    if (is_string($listener)) {
                        $listener = implode(',', $listener);
                    }
                    foreach ($listener as $l) {
                        static::$instance->listen($event, $l);
                    }
                }
            }
        }
        return static::$instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::$instance->{$name}(... $arguments);
    }
}