<?php

namespace Scraper\Database;


class Factory
{
    /**
     * @var Database $instance
     */
    private static $instance;

    /**
     * @param $type
     * @param $settings
     * @return Database
     */
    public static function getDatabase($type, $settings)
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