<?php

namespace Scraper\Lock;

/**
 * Class Factory
 * @package Scraper\Lock
 * @author Joost Mul <scraper@jmul.net>
 */
final class Factory
{
    /**
     * @var Lock
     */
    private static $instance;

    /**
     * @param string $type
     * @return Lock
     */
    public static function getLocker(string $type) : Lock
    {
        if (self::$instance instanceof Lock) {
            return self::$instance;
        }

        switch ($type) {
            case Normal::getName():
            default:
                self::$instance = new Normal();
                break;
        }

        return self::$instance;
    }
}