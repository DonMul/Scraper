<?php

namespace Scraper\Database;
use Scraper\Data\Backlog;
use Scraper\Data\Link;
use Scraper\Data\Page;
use Scraper\Data\Site;

/**
 * Interface Database
 * @package Scraper\Database
 * @author Joost Mul <scraper@jmul.net>
 */
interface Database
{
    /**
     * Database constructor.
     * @param array $settings
     */
    public function __construct(array $settings);

    /**
     * @return string
     */
    public static function getName();

    /**
     * @param string $excludedPath
     * @return array
     */
    public function getRandomUnlockedBacklogItem(string $excludedPath = '') : ?array;

    /**
     * @param Backlog $item
     * @return bool
     */
    public function lockBacklogItem(Backlog $item) : bool;

    /**
     * @param Backlog $item
     * @return bool
     */
    public function saveBacklogItem(Backlog $item) : bool;

    /**
     * @param Backlog $item
     * @return bool
     */
    public function deleteBacklogItem(Backlog $item) : bool;

    /**
     * @param Link $link
     * @return bool
     */
    public function saveLink(Link $link) : bool;

    /**
     * @param Site   $site
     * @param string $url
     * @return array
     */
    public function getSiteBySiteAndUrl(Site $site, string $url) : ?array;

    /**
     * @param Page $page
     * @return Page
     */
    public function createPage(Page $page) : Page;

    /**
     * @param Page $page
     */
    public function updatePage(Page $page);

    /**
     * @param string $url
     * @return array
     */
    public function getSiteByUrl(string $url) : ?array;

    /**
     * @param Site $site
     * @return Site
     */
    public function createSite(Site $site) : Site;

    /**
     * @param Site $site
     */
    public function updateSite(Site $site);
}