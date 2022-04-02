<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Webman;

use Illuminate\Support\Arr;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class Config
{
    /**
     * @var array
     */
    protected static array $items = [];

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return Arr::has(self::$items, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array|string $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            return self::getMany($key);
        }

        return Arr::get(self::$items, $key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    public static function set(array|string $key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set(self::$items, $key, $value);
        }
    }

    /**
     * Get many configuration values.
     *
     * @param array $keys
     * @return array
     */
    private static function getMany(array $keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get(static::$items, $key, $default);
        }

        return $config;
    }


    public static function load()
    {
        $files = static::getConfigurationFiles();
        foreach ($files as $key => $path) {
            static::set($key, require $path);
        }
    }


    private static function getConfigurationFiles()
    {
        $files = [];

        $configPath = config_path();

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = static::getNestedDirectory($file, $configPath);

            $files[$directory . basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    private static function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested) . '.';
        }

        return $nested;
    }

}
