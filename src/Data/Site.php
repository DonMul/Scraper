<?php

namespace Scraper\Data;

use Scraper\Cache;
use Scraper\Database\Database;
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
     * @param Database $database
     * @return null|Site
     */
    public static function getByUrl($url, Database $database)
    {
        if ($site = Cache\Factory::getInstance()->get('site-url-' . $url)) {
            return $site;
        }

        $result = $database->getSiteByUrl($url);
        if ($result) {
            $site = self::convertToObject($result);
            Cache\Factory::getInstance()->set('site-url-' . $url, $site);
            return $site;
        }


        return null;
    }

    /**
     * @param Database $database
     * @return bool
     */
    public function save(Database $database)
    {
        if (!$this->getId()) {
            $newSite = $database->createSite($this);
        } else {
            $database->updateSite($this);
            $newSite = $this;
        }

        return Cache\Factory::getInstance()->set('site-url-' . $this->getUrl(), $newSite);
    }

    /**
     * @param array $data
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
     * @param Database $database
     * @return null|Site
     */
    public static function ensureByUrl($url, Database $database)
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