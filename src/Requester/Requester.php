<?php

namespace Scraper\Requester;

use Scraper\Data\Backlog;
use Scraper\Logger\Logger;

/**
 * Interface Requester
 * @package Scraper\Requester
 * @author Joost Mul <scraper@jmul.net>
 */
interface Requester
{
    /**
     * Requester constructor.
     * @param Logger $logger
     * @param array  $settings
     */
    public function __construct(Logger $logger, array $settings = []);

    /**
     * @param Backlog $item
     * @return string
     */
    public function getContents(Backlog $item);

    /**
     * @return string
     */
    public static function getName() : string;
}