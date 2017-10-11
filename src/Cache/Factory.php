<?php

namespace Scraper\Cache;

/**
 * Class Factory
 * @package Scraper\Cache
 */
class Factory
{
    /**
     * @var Cache $instance
     */
    private static $instance;

    /**
     * @param string $type
     * @return Cache
     */
    public static function getInstance($type = '')
    {
        if (self::$instance instanceof Cache) {
            return self::$instance;
        }

        switch ($type) {
            case Memory::getName():
            default:
                self::$instance = Memory::getInstance();
                break;
        }

        return self::$instance;
    }
}