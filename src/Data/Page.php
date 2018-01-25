<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\Database;
use Scraper\Util;

/**
 * Class Page
 * @package Scraper\Data
 * @author Joost Mul <scraper@jmul.net>
 */
final class Page
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $siteId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * Page constructor.
     * @param int       $id
     * @param int       $siteId
     * @param string    $url
     * @param string    $title
     */
    public function __construct(?int $id, int $siteId, string $url, string $title)
    {
        $this->setId($id);
        $this->setSiteId($siteId);
        $this->setUrl($url);
        $this->setTitle($title);
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
     * @return int
     */
    public function getSiteId() : int
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId(int $siteId)
    {
        $this->siteId = $siteId;
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
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}