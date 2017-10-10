<?php

namespace Scraper\Requester;

use Scraper\Data\Backlog;
use Scraper\Logger\Logger;

/**
 * Interface Requester
 * @package Scraper\Requester
 */
interface Requester
{
    /**
     * Requester constructor.
     * @param Logger $logger
     * @param array  $settings
     */
    public function __construct(Logger $logger, $settings = []);

    /**
     * @param Backlog $item
     * @return string
     */
    public function getContents(Backlog $item);

    /**
     * @return string
     */
    public static function getName();
}