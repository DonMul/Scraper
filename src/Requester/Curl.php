<?php

namespace Scraper\Requester;

use Scraper\Data\Backlog;
use Scraper\Logger\Logger;

/**
 * Class Curl
 * @package Scraper\Requester
 * @author Joost Mul <scraper@jmul.net>
 */
final class Curl implements Requester
{
    const NAME = 'cURL';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Curl constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger, array $settings = [])
    {
       $this->logger = $logger;
    }

    /**
     * @param Backlog $item
     * @return mixed
     */
    public function getContents(Backlog $item) : string
    {
        $ch = curl_init($item->getLink());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
       return self::NAME;
    }
}