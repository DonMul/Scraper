<?php
namespace Scraper\Cache;

interface Cache
{
    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value);

    /**
     * @return string
     */
    public static function getName();
}