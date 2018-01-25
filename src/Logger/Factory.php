<?php

namespace Scraper\Logger;

/**
 * Class Factory
 * @package Scraper\Logger
 * @author Joost Mul <scraper@jmul.net>
 */
final class Factory
{
    /**
     * @var Logger $instance
     */
    private static $instance;

    /**
     * @param string $loggerName
     * @return Logger
     */
    public static function getLogger($loggerName) : Logger
    {
        if (self::$instance instanceof Logger) {
            return self::$instance;
        }

        switch ($loggerName) {
            case StdOut::getName():
                self::$instance = new StdOut();
                break;
            case NoLogger::getName():
            default:
                self::$instance = new NoLogger();
                break;
        }

        return self::$instance;
    }
}