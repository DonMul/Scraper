<?php

namespace Scraper;

/**
 * Class Cache
 * @author Joost Mul <scraper@jmul.net>
 */
final class Cache
{
    /**
     * @var Cache
     */
    private static $instance;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Cache constructor.
     */
    private function __construct()
    {

    }

    /**
     * @return Cache
     */
    public static function getInstance()
    {
        if (self::$instance instanceof Cache) {
            return self::$instance;
        }

        self::$instance = new Cache;
        return self::$instance;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->clearCacheIfNeeded();
        $this->cache[$key] = $value;
    }

    /**
     *
     */
    public function clearCacheIfNeeded()
    {
        $memoryLimit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
            if ($matches[2] == 'M') {
                $memoryLimit = $matches[1] * 1024 * 1024;
            } else if ($matches[2] == 'K') {
                $memoryLimit = $matches[1] * 1024;
            }
        }

        if (memory_get_usage(true) / $memoryLimit > 0.9) {
            $this->cache = [];
        }
    }
}