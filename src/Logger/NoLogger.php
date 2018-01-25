<?php

namespace Scraper\Logger;

/**
 * Class NoLogger
 * @package Scraper\Logger
 * @author Joost Mul <scraper@jmul.net>
 */
final class NoLogger implements Logger
{
    const NAME = 'NoLogger';

    /**
     * Stop any message
     *
     * @param string $tag
     * @param string $message
     */
    public function log(string $tag, string $message)
    {

    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return self::NAME;
    }
}