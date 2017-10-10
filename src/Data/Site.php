<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\MySQL;
use Scraper\Util;

/**
 * Class Site
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
    public function __construct($id, $url)
    {
        $this->setId($id);
        $this->setUrl($url);
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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @throws \Exception
     */
    public function setUrl($url)
    {
        $url = trim(strtolower($url));
        $url = rtrim($url, '.');
        if (!preg_match('/[a-zA-Z\-\.0-9]+\.[a-z]+/', $url)) {
            throw new \Exception("Invalid url given: {$url}");
        }

        $this->url = $url;
    }

    /**
     * @param string $url
     * @param MySQL $database
     * @return null|Site
     */
    public static function getByUrl($url, MySQL $database)
    {
        if ($site = Cache::getInstance()->get('site-url-' . $url)) {
            return $site;
        }

        $result = $database->fetchOne("SELECT * FROM site WHERE url = ? LIMIT 1", [$url] , 's');
        if ($result) {
            $site = self::convertToObject($result);
            Cache::getInstance()->set('site-url-' . $url, $site);
            return $site;
        }


        return null;
    }

    /**
     * @param MySQL $database
     * @return bool
     */
    public function save(MySQL $database)
    {
        if (!$this->getId()) {
            $result = $database->query("INSERT INTO site (`url`) VALUES ( ? )", [$this->getUrl()], 's');
        } else {
            $result = $database->query("REPLACE INTO site (`id`, `url`) VALUES ( ?, ? )", [
                $this->getId(),
                $this->getUrl()
            ], 'is');
        }

        $this->setId($result->insert_id);
        Cache::getInstance()->set('site-url-' . $this->getUrl(), $this);
        return $result->affected_rows > 0;
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public static function getAmount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM site");
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public function getIncomingLinksCount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM link JOIN page ON page.id = link.toPageId WHERE siteId = ?", [$this->getId()], 'i');
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public function getPagesCount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM page WHERE siteId = ?", [$this->getId()], 'i');
        return $result['cnt'];
    }
    /**
     * @param MySQL $database
     * @return int
     */
    public function getIncomingSitesCount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(DISTINCT p1.siteId) AS cnt FROM link JOIN page p1 ON p1.id = link.fromPageId JOIN page p2 ON p2.id = link.toPageId WHERE p2.siteId = ?", [$this->getId()], 'i');
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public function getOutgoingLinksCount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM link JOIN page ON page.id = link.fromPageId WHERE siteId = ?", [$this->getId()], 'i');
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public function getOutgoingSitesCount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(DISTINCT p1.siteId) AS cnt FROM link JOIN page p1 ON p1.id = link.fromPageId JOIN page p2 ON p2.id = link.toPageId WHERE p1.siteId = ?", [$this->getId()], 'i');
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return string[]
     */
    public static function getAllUrls(MySQL $database)
    {
        $result = $database->fetchAll("SELECT DISTINCT url FROM site");
        $urls = [];
        foreach ($result as $url) {
            $urls[] = $url['url'];
        }

        sort($urls);
        return $urls;
    }
    /**
     * @param string $data
     * @return Site
     */
    private static function convertToObject($data)
    {
        return new Site(
            Util::arrayGet($data, 'id'),
            Util::arrayGet($data, 'url')
        );
    }

    /**
     * @param string $url
     * @param MySQL $database
     * @return null|Site
     */
    public static function ensureByUrl($url, MySQL $database)
    {
        $site = self::getByUrl($url, $database);
        if (!$site) {
            $site = new Site(
                null,
                $url
            );
            $site->save($database);
        }

        return $site;
    }
}