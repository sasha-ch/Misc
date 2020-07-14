<?php

/**
 *  Singletone DIC based on simple ArrayAccess object container (Pimple)
 */
class Container
{

    static $container = null;

    private function __construct()
    {
        return false;
    }

    /**
     * Get container obj
     *
     * @param $configFile php config file
     *
     * @return mixed|null
     */
    public static function getContainer($configFile)
    {
        if (self::$container == null) {
            self::$container = require $configFile;
        }
        return self::$container;
    }

    /**
     * Get var from container
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        $container = self::getContainer();
        return $container[$key];
    }

    /**
     * Set var to container
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function set($key, $value)
    {
        $container = self::getContainer();
        $container[$key] = $value;
        self::$container = $container;
    }
}