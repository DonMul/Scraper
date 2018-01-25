<?php

namespace Scraper\Repository;

use Scraper\Database\Database;
use Scraper\Data;
use Scraper\Cache;
use Scraper\Util;

/**
 * Class Page
 * @package Scraper\Repository
 * @author Joost Mul <scraper@jmul.net>
 */
final class Page
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
     * @param Data\Site $site
     * @param string $url
     * @return Data\Page
     */
    public function ensureBySiteAndUrl(Data\Site $site, string $url) : Data\Page
    {
        $page = $this->getBySiteAndUrl($site, $url);
        if (!$page) {
            $page = new Data\Page(
                null,
                $site->getId(),
                $url,
                ''
            );
            $this->persist($page);
        }

        return $page;
    }

    /**
     * @param Data\Page $page
     */
    public function persist(Data\Page $page)
    {
        if (!$page->getId()) {
            $newPage = $this->database->createPage($page);
        } else {
            $this->database->updatePage($page);
            $newPage = $page;
        }

        Cache\Factory::getInstance()->set("page-siteId-{$page->getSiteId()}-url-{$page->getUrl()}", $newPage);
    }

    /**
     * @param Data\Site $site
     * @param string $url
     * @return Data\Page
     */
    public function getBySiteAndUrl(Data\Site $site, string $url) : ?Data\Page
    {
        if ($page = Cache\Factory::getInstance()->get("page-siteId-{$site->getId()}-url-{$url}")) {
            return $page;
        }


        $result = $this->database->getSiteBySiteAndUrl($site, $url);

        if ($result) {
            $page = $this->convertToObject($result);
            Cache\Factory::getInstance()->set("page-siteId-{$site->getId()}-url-{$url}", $page);
            return $page;
        }

        return null;
    }

    /**
     * @param array $data
     * @return Data\Page
     */
    private function convertToObject($data) : Data\Page
    {
        return new Data\Page(
            Util::arrayGet($data, 'id'),
            Util::arrayGet($data, 'siteId'),
            Util::arrayGet($data, 'url'),
            Util::arrayGet($data, 'title')
        );
    }
}