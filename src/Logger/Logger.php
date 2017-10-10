<?php

namespace Scraper\Logger;

/**
 * Interface Logger
 * @package Scraper\Logger
 */
interface Logger
{
    const TAG_INFO = 'INFO';
    const TAG_ERRO = 'ERRO';
    const TAG_WARN = 'WARN';
    const TAG_SUCC = 'SUCC';

    /**
     * @param string $tag
     * @param string $message
     */
    public function log($tag, $message);

    /**
     * @return string
     */
    public static function getName();
}