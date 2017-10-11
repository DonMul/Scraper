<?php

namespace Scraper\Cache;

/**
 * Class Cache
 * @author Joost Mul <scraper@jmul.net>
 */
final class Memory implements Cache
{
    const NAME = 'Memory';

    /**
     * Static instance of the cacher.
     *
     * @var Cache
     */
    private static $instance;

    /**
     * Current Cache
     *
     * @var array
     */
    private $cache = [];

    /**
     * Cache constructor.
     */
    private function __construct()
    {
        $this->cache = [];
    }

    /**
     * Returns the Cache instance.
     *
     * @return Cache
     */
    public static function getInstance()
    {
        if (self::$instance instanceof Cache) {
            return self::$instance;
        }

        self::$instance = new Memory();
        return self::$instance;
    }

    /**
     * Returns a cached object based on the given key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * Sets the cache with the given key to the given value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->clearCacheIfNeeded();
        $this->cache[$key] = $value;
    }

    /**
     * Clears cache if mem usage gets too high
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

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }
}