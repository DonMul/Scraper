<?php

namespace Scraper\Repository;

use Scraper\Database\Database;
use Scraper\Data;
use Scraper\Cache;
use Scraper\Util;

/**
 * Class Site
 * @package Scraper\Repository
 * @author Joost Mul <scraper@jmul.net>
 */
final class Site
{
    /**
     * @var Database
     */
    private $database;

    /**
     * Backlog constructor.
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $url
     * @return Data\Site
     */
    public function ensureByUrl(string $url) : Data\Site
    {
        $site = $this->getByUrl($url);
        if (!$site) {
            $site = new Data\Site(
                null,
                $url
            );
            $this->persist($site);
        }

        return $site;
    }

    /**
     * @param Data\Site $site
     */
    public function persist(Data\Site $site)
    {
        if (!$site->getId()) {
            $newSite = $this->database->createSite($site);
        } else {
            $this->database->updateSite($site);
            $newSite = $site;
        }

        Cache\Factory::getInstance()->set('site-url-' . $site->getUrl(), $newSite);
    }

    /**
     * @param string $url
     * @return Data\Site
     */
    public function getByUrl($url) : ?Data\Site
    {
        if ($site = Cache\Factory::getInstance()->get('site-url-' . $url)) {
            return $site;
        }

        $result = $this->database->getSiteByUrl($url);
        if ($result) {
            $site = $this->convertToObject($result);
            Cache\Factory::getInstance()->set('site-url-' . $url, $site);
            return $site;
        }


        return null;
    }

    /**
     * @param array $data
     * @return Data\Site
     */
    private function convertToObject($data) : Data\Site
    {
        return new Data\Site(
            Util::arrayGet($data, 'id'),
            Util::arrayGet($data, 'url')
        );
    }
}