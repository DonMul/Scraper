<?php

namespace Scraper\Logger;

/**
 * Class StdOut
 * @package Scraper\Logger
 * @author Joost Mul <scraper@jmul.net>
 */
final class StdOut implements Logger
{
    const NAME = 'StdOut';

    /**
     * @var bool
     */
    private $loggingEnabled = true;

    /**
     * Logger constructor.
     * @param bool $loggingEnabled
     */
    public function __construct(bool $loggingEnabled = true)
    {
        $this->loggingEnabled = $loggingEnabled;
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return self::NAME;
    }

    /**
     * Send a debug message to stdOut.
     *
     * @param string $tag
     * @param string $message
     */
    public function log(string $tag, string$message)
    {
        if ($this->loggingEnabled == true) {
            $time = microtime(true);
            $microTime = sprintf("%06d", ($time - floor($time)) * 1000000);
            $date = new \DateTime(date('Y-m-d H:i:s.' . $microTime, $time));
            $color = $this->getColorForTag($tag);
            $message = "\033[{$color}[" . $date->format("Y-m-d H:i:s.u") . "] [{$tag}] {$message} \033[0m". PHP_EOL;

            fwrite(STDOUT, $message);
        }
    }

    /**
     * @param string $tag
     * @return string
     */
    private function getColorForTag(string $tag) : string
    {
        switch ($tag) {
            case self::TAG_WARN:
                return '33m';
            case self::TAG_ERRO:
                return '31m';
            case self::TAG_SUCC:
                return '32m';
            case self::TAG_INFO:
                return '94m';
            default:
                return '39m';
        }
    }
}