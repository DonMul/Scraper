<?php

namespace Scraper\Database;

/**
 * Class Factory
 * @package Scraper\Database
 * @author Joost Mul <scraper@jmul.net>
 */
final class Factory
{
    /**
     * @var Database $instance
     */
    private static $instance;

    /**
     * @param string $type
     * @param array $settings
     * @return Database
     */
    public static function getDatabase(string $type, array $settings)
    {
        if (self::$instance instanceof Database) {
            return self::$instance;
        }

        switch ($type) {
            case MySQL::getName():
                self::$instance = new MySQL($settings);
                break;
        }

        return self::$instance;
    }
}