<?php

namespace Scraper\Logger;

/**
 * Class Logger
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
    public function log($tag, $message)
    {

    }

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }
}