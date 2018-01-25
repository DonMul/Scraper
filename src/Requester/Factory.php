<?php

namespace Scraper\Requester;
use Scraper\Logger\Logger;

/**
 * Class Factory
 * @package Scraper\Requester
 * @author Joost Mul <scraper@jmul.net>
 */
final class Factory
{
    /**
     * @param string $type
     * @param Logger $logger
     * @param array  $settings
     * @return Requester
     */
    public static function getRequester($type, Logger $logger, array $settings) : Requester
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