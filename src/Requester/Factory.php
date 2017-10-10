<?php

namespace Scraper\Requester;
use Scraper\Logger\Logger;

/**
 * Class Factory
 * @package Scraper\Requester
 */
class Factory
{
    /**
     * @param string $type
     * @param Logger $logger
     * @param array  $settings
     * @return Requester
     */
    public static function getRequester($type, Logger $logger, $settings)
    {
        switch ($type) {
            case Curl::getName():
                return new Curl($logger, $settings);
                break;
            case Tor::getName():
                return new Tor($logger, $settings);
                break;
        }

        return null;
    }
}