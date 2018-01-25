<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\Database;
use Scraper\Util;

/**
 * Class Site
 * @package Scraper\Data
 * @author Joost Mul <scraper@jmul.net>
 */
final class Site
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * Site constructor.
     * @param int    $id
     * @param string $url
     */
    public function __construct(?int $id, string $url)
    {
        $this->setId($id);
        $this->setUrl($url);
    }

    /**
     * @return int
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @throws \Exception
     */
    public function setUrl(string $url)
    {
        $url = trim(strtolower($url));
        $url = rtrim($url, '.');
        if (!preg_match('/[a-zA-Z\-\.0-9]+\.[a-z]+/', $url)) {
            throw new \Exception("Invalid url given: {$url}");
        }

        $this->url = $url;
    }
}