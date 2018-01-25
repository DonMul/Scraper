<?php
namespace Scraper\Cache;

/**
 * Interface Cache
 * @package Scraper\Cache
 * @author Joost Mul <scraper@jmul.net>
 */
interface Cache
{
    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value);

    /**
     * @return string
     */
    public static function getName() : string;
}