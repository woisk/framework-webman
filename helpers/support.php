<?php
declare(strict_types=1);


use Webman\Config;
use Webman\Container\Container;

if (!function_exists('config')) {

    /**
     * @param string|array|null $key
     * @param mixed $default
     ********************************************
     */
    function config(string|array $key = null, mixed $default = null)
    {
        if (is_null($key)) {
            return new Config();
        }

        if (is_array($key)) {
            Config::set($key);
        }

        return Config::get($key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed|Webman\Container\Container
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}