<?php
declare(strict_types=1);

namespace Webman\Support;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Connection;
use Workerman\Timer;
use Workerman\Worker;

class LaravelDb
{
    /**
     * @param Worker $worker
     * @return void
     */
    public static function instance($worker)
    {
        if (!class_exists('\Illuminate\Database\Capsule\Manager')) {
            return;
        }

        $connections = config('database.connections');
        if (!$connections) {
            return;
        }


        $capsule = new Capsule;
        $configs = config('database');

        $capsule->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;

            return new Connection($config);
        });

        if (isset($configs['default'])) {
            $default_config = $connections[$configs['default']];
            $capsule->addConnection($default_config);
        }

        foreach ($connections as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        if (class_exists('\Illuminate\Events\Dispatcher')) {
            $capsule->setEventDispatcher(new Dispatcher(new Container));
        }

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        //Heartbeat
        if ($worker) {
            Timer::add(55, function () use ($connections) {
                foreach ($connections as $key => $item) {
                    if ($item['driver'] == 'mysql') {
                        Db::connection($key)->select('select 1');
                    }
                }
            });
        }
    }
}