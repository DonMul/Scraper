<?php

namespace Scraper;

use Scraper\Data\Backlog;
use Scraper\Data\Link;
use Scraper\Data\Page;
use Scraper\Data\Site;
use Scraper\Database\Database;
use Scraper\Requester\Requester;
use Scraper\Logger\Logger;

/**
 * Class Processor
 * @author Joost Mul <scraper@jmul.net>
 */
final class Processor
{
    /**
     * The database connection used to interact with the databse
     *
     * @var Database
     */
    private $database;

    /**
     * The logger used to log possible information
     *
     * @var Logger
     */
    private $logger;

    /**
     * The requester which actually performs all request
     *
     * @var Requester
     */
    private $requester;

    /**
     * @var array
     */
    private $settings = [];

    /**
     * Processor constructor.
     *
     * @param Database $database
     * @param Logger $logger
     */
    public function __construct(Database $database, Logger $logger, Requester $requester, $settings = [])
    {
        $this->database     = $database;
        $this->logger       = $logger;
        $this->requester    = $requester;
        $this->settings     = $settings;
    }

    /**
     * Processes the given Backlog item by retrieving all links and adding them to the backlog and storing all
     * connections between sites.
     *
     * @param Backlog $item
     */
    public function processBacklogItem(Backlog $item) {
        if (!$this->shouldProcessItem($item)) {
            $this->logger->log(Logger::TAG_INFO, "Skipping {$item->getUrl()} because of site blacklist");
            return;
        }

        $content = $this->getPageContentsForItem($item);
        if (!$content) {
            return;
        }

        $links = $this->getLinksFromContent($content);
        $title = $this->getTitleFromContent($content);

        foreach ($links as $link) {
            $this->logger->log(Logger::TAG_INFO, "Found: " . Util::arrayGet(parse_url($link->getAttribute('href')), 'path'));
            $this->processLinkForBacklogItem($link, $item, $title);
        }
    }

    /**
     * Process a link found in a retrieved site
     *
     * @param \DOMElement   $link
     * @param Backlog       $item
     * @param string        $title
     */
    private function processLinkForBacklogItem(\DOMElement $link, Backlog $item, $title)
    {
        $isInternal = false;
        $fromSite = Site::ensureByUrl($item->getUrl(), $this->database);
        $fromPage = Page::ensureBySiteAndUrl($fromSite, $item->getPath(), $this->database);

        if ($fromPage->getTitle() == '') {
            $fromPage->setTitle($title);
            $fromPage->save($this->database);
        }

        $toLink = $link->getAttribute('href');
        $urlData = parse_url($toLink);


        if (!isset($urlData['host'])) {
            $toSite = $fromSite;
            $isInternal = true;
        } else {
            $toSite = Site::ensureByUrl($urlData['host'], $this->database);
        }

        if (in_array($toSite->getUrl(), Util::arrayGet($this->settings, 'skipSites', []))) {
            $this->logger->log(Logger::TAG_INFO, "Skipping {$toSite->getUrl()} because of site blacklist");
            return;
        }

        if (!isset($urlData['path'])) {
            $urlData['path'] = '/';
        }

        $toUrl = trim($urlData['path']);
        if ($this->shouldProcessUrl($fromSite, $toSite, $fromPage->getUrl(), $toUrl)) {
            $isAlreadyExisting = Page::getBySiteAndUrl($toSite, $urlData['path'], $this->database) !== null;
            $toPage = Page::ensureBySiteAndUrl($toSite, $urlData['path'], $this->database);

            $newdoc = new \DOMDocument();
            $cloned = $link->cloneNode(true);
            $newdoc->appendChild($newdoc->importNode($cloned,TRUE));

            $link = new Link(
                $fromPage->getId(),
                $toPage->getId(),
                $toPage->getUrl(),
                $toUrl,
                $newdoc->saveHTML(),
                $isInternal
            );

            $link->save($this->database);

            if (!$isAlreadyExisting) {
                $backlogItem = new Backlog('http://' . $toSite->getUrl() . $toPage->getUrl(), false);
                if ($this->shouldProcessItem($backlogItem)) {
                    $backlogItem->save($this->database);
                }
            }

            $this->logger->log(Logger::TAG_SUCC, "New Link " . $fromSite->getUrl() . $fromPage->getUrl() . ' -> ' . $toSite->getUrl() . $toPage->getUrl());
        } else {
            $this->logger->log(Logger::TAG_WARN, "Skipping "  . $toUrl);
        }
    }

    /**
     * @param Backlog $item
     * @return string
     */
    private function getPageContentsForItem(Backlog $item)
    {
        return $this->requester->getContents($item);
    }

    /**
     * Returns the title from the DOM page.
     *
     * @param string $content
     * @return string
     */
    private function getTitleFromContent($content)
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_use_internal_errors(false);

        $titleElements = $dom->getElementsByTagName('title');
        $title = '';
        foreach ($titleElements as $titleElement) {
            $title = $titleElement->nodeValue;
        }

        return $title;
    }

    /**
     * Returns a DOMNodeList with all links found in the DOMDocument
     *
     * @param string $content
     * @return \DOMNodeList
     */
    private function getLinksFromContent($content)
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_use_internal_errors(false);

        $links = $dom->getElementsByTagName('a');

        return $links;
    }

    /**
     * Whether or not the given link should be processed.
     *
     * @param Site      $fromSite
     * @param Site      $toSite
     * @param string    $fromUrl
     * @param string    $toUrl
     * @return bool
     */
    private function shouldProcessUrl(Site $fromSite, Site $toSite, $fromUrl, $toUrl)
    {
        if (empty($toUrl)) {
            $this->logger->log(Logger::TAG_WARN, 'SKIP: Url empty');
            return false;
        }

        if (strpos($toUrl, '#') === 0) {
            $this->logger->log(Logger::TAG_WARN, 'SKIP: Starts with #: ' . $toUrl);
            return false;
        }

        if (strpos($toUrl, 'javascript:') === 0) {
            $this->logger->log(Logger::TAG_WARN, 'SKIP: Starts with javascript: ' . $toUrl);
            return false;
        }

        if (preg_match('/^[a-zA-Z0-9]+\([a-zA-Z0-9]+\);/', $toUrl)) {
            $this->logger->log(Logger::TAG_WARN, 'SKIP: Contains javascript: ' . $toUrl);
        }

        $fromUrlMatches = [];
        preg_match('/^(.+)[\?#]?/', $fromUrl, $fromUrlMatches);
        $cleanedFromUrl = $fromUrlMatches[1];

        $toUrlMatches = [];
        preg_match('/^(.+)[\?#]?/', $toUrl, $toUrlMatches);
        $cleanedToUrl = $toUrlMatches[1];

        if ($cleanedFromUrl == $cleanedToUrl && $fromSite->getId() == $toSite->getId()) {
            $this->logger->log(Logger::TAG_WARN, "SKIP: {$fromSite->getUrl()}{$cleanedFromUrl} == {$toSite->getUrl()}{$cleanedToUrl}");
            return false;
        }

        if (preg_match('/^.+\.(pdf|doc|odt|docx|xml|json|mp[0-9]+)$/', $toUrl)) {
            return false;
        }

        if (strpos($toUrl, 'mailto') === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Backlog $item
     * @return bool
     */
    private function shouldProcessItem(Backlog $item)
    {
        $data = parse_url($item->getUrl());
        $path = Util::arrayGet($data, 'path', '');
        if (empty($path)) {
            return false;
        }

        foreach (Util::arrayGet($this->settings, ['skipSites'], []) as $skipSite) {
            if (preg_match("@{$skipSite}@", $path)) {
                return false;
            }
        }

        return true;
    }
}