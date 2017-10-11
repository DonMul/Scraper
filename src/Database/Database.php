<?php

namespace Scraper\Database;
use Scraper\Data\Backlog;
use Scraper\Data\Link;
use Scraper\Data\Page;
use Scraper\Data\Site;

/**
 * Interface Database
 * @package Scraper\Database
 */
interface Database
{
    /**
     * @return string
     */
    public static function getName();

    /**
     * @return array
     */
    public function getRandomUnlockedBacklogItem();

    /**
     * @param Backlog $item
     * @return bool
     */
    public function lockBacklogItem(Backlog $item);

    /**
     * @param Backlog $item
     * @return bool
     */
    public function saveBacklogItem(Backlog $item);

    /**
     * @param Backlog $item
     * @return bool
     */
    public function deleteBacklogItem(Backlog $item);

    /**
     * @param Link $link
     * @return bool
     */
    public function saveLink(Link $link);

    /**
     * @param Site   $site
     * @param string $url
     * @return array
     */
    public function getSiteBySiteAndUrl(Site $site, $url);

    /**
     * @param Page $page
     * @return Page
     */
    public function createPage(Page $page);

    /**
     * @param Page $page
     */
    public function updatePage(Page $page);

    /**
     * @param string $url
     * @return array
     */
    public function getSiteByUrl($url);

    /**
     * @param Site $site
     * @return Site
     */
    public function createSite(Site $site);

    /**
     * @param Site $site
     */
    public function updateSite(Site $site);
}