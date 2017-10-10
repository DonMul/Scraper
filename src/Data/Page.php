<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\MySQL;
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
     * @param MySQL $database
     * @return int
     */
    public static function getAmount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM page");
        return $result['cnt'];
    }

    /**
     * @param Site      $site
     * @param string    $url
     * @param MySQL     $database
     * @return Page
     */
    public static function getBySiteAndUrl(Site $site, $url, MySQL $database) {
        if ($page = Cache::getInstance()->get("page-siteId-{$site->getId()}-url-{$url}")) {
            return $page;
        }

        $result = $database->fetchOne("SELECT * FROM page WHERE siteId = ? AND url = ?", [
            $site->getId(),
            $url
        ], 'is');

        if ($result) {
            $page = self::convertToObject($result);
            Cache::getInstance()->set("page-siteId-{$site->getId()}-url-{$url}", $page);
            return $page;
        }

        return null;
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public function save(MySQL $database)
    {
        if (!$this->getId()) {
            $result = $database->query("INSERT INTO page (`title`, `url`, `siteId`) VALUES ( ?, ?, ?)", [
                $this->getTitle(),
                $this->getUrl(),
                $this->getSiteId()
            ], 'ssi');

            $this->setId($result->insert_id);
        } else {
            $result = $database->query("REPLACE INTO page (`id`, `title`, `url`, `siteId`) VALUES ( ?, ?, ?, ?)", [
                $this->getId(),
                $this->getTitle(),
                $this->getUrl(),
                $this->getSiteId()
            ], 'issi');
        }

        Cache::getInstance()->set("page-siteId-{$this->getSiteId()}-url-{$this->getUrl()}", $this);
        return $result->affected_rows;
    }

    /**
     * @param Site      $site
     * @param string    $url
     * @param MySQL     $database
     * @return Page
     */
    public static function ensureBySiteAndUrl(Site $site, $url, MySQL $database)
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
    private function convertToObject($data)
    {
        return new Page(
            Util::arrayGet($data, 'id'),
            Util::arrayGet($data, 'siteId'),
            Util::arrayGet($data, 'url'),
            Util::arrayGet($data, 'title')
        );
    }
}