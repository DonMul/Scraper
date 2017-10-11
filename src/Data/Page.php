<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\Database;
use Scraper\Util;

/**
 * Class Page
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
    public function __construct($id, $siteId, $url, $title)
    {
        $this->setId($id);
        $this->setSiteId($siteId);
        $this->setUrl($url);
        $this->setTitle($title);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param Site      $site
     * @param string    $url
     * @param Database $database
     * @return Page
     */
    public static function getBySiteAndUrl(Site $site, $url, Database $database) {
        if ($page = Cache\Factory::getInstance()->get("page-siteId-{$site->getId()}-url-{$url}")) {
            return $page;
        }


        $result = $database->getSiteBySiteAndUrl($site, $url);

        if ($result) {
            $page = self::convertToObject($result);
            Cache\Factory::getInstance()->set("page-siteId-{$site->getId()}-url-{$url}", $page);
            return $page;
        }

        return null;
    }

    /**
     * @param Database $database
     */
    public function save(Database $database)
    {
        if (!$this->getId()) {
            $newPage = $database->createPage($this);
        } else {
            $database->updatePage($this);
            $newPage = $this;
        }

        Cache\Factory::getInstance()->set("page-siteId-{$this->getSiteId()}-url-{$this->getUrl()}", $newPage);
    }

    /**
     * @param Site      $site
     * @param string    $url
     * @param Database  $database
     * @return Page
     */
    public static function ensureBySiteAndUrl(Site $site, $url, Database $database)
    {
        $page = self::getBySiteAndUrl($site, $url, $database);
        if (!$page) {
            $page = new Page(
                null,
                $site->getId(),
                $url,
                ''
            );
            $page->save($database);
        }

        return $page;
    }

    /**
     * @param array $data
     * @return Page
     */
    private static function convertToObject($data)
    {
        return new Page(
            Util::arrayGet($data, 'id'),
            Util::arrayGet($data, 'siteId'),
            Util::arrayGet($data, 'url'),
            Util::arrayGet($data, 'title')
        );
    }
}