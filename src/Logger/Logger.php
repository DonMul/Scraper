<?php

namespace Scraper\Logger;

/**
 * Interface Logger
 * @package Scraper\Logger
 * @author Joost Mul <scraper@jmul.net>
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
    public function log(string $tag, string $message);

    /**
     * @return string
     */
    public static function getName() : string;
}