<?php

namespace Scraper;

use Scraper\Data\Backlog;
use Scraper\Repository;
use Scraper\Data\Link;
use Scraper\Data\Site;
use Scraper\Requester\Requester;
use Scraper\Logger\Logger;

/**
 * Class Processor
 * @package Scraper
 * @author Joost Mul <scraper@jmul.net>
 */
final class Processor
{
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
     * @var Repository\Backlog
     */
    private $backlogRepo;

    /**
     * @var Repository\Link
     */
    private $linkRepo;

    /**
     * @var Repository\Page
     */
    private $pageRepo;

    /**
     * @var Repository\Site
     */
    private $siteRepo;

    /**
     * Processor constructor.
     *
     * @param Logger $logger
     * @param Requester $requester
     * @param Repository\Backlog $backlogRepo
     * @param Repository\Link $linkRepo
     * @param Repository\Page $pageRepo
     * @param Repository\Site $siteRepo
     * @param array $settings
     */
    public function __construct(
        Logger $logger,
        Requester $requester,
        Repository\Backlog $backlogRepo,
        Repository\Link $linkRepo,
        Repository\Page $pageRepo,
        Repository\Site $siteRepo,
        $settings = []
    ) {
        $this->logger       = $logger;
        $this->requester    = $requester;
        $this->settings     = $settings;
        $this->backlogRepo  = $backlogRepo;
        $this->linkRepo     = $linkRepo;
        $this->pageRepo     = $pageRepo;
        $this->siteRepo     = $siteRepo;
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
            $this->logger->log(Logger::TAG_ERRO, "No contents retreived for {$item->getUrl()}");
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
    private function processLinkForBacklogItem(\DOMElement $link, Backlog $item, string $title)
    {
        $isInternal = false;
        $fromSite = $this->siteRepo->ensureByUrl($item->getUrl());
        $fromPage = $this->pageRepo->ensureBySiteAndUrl($fromSite, $item->getPath());

        if ($fromPage->getTitle() == '') {
            $fromPage->setTitle($title);
            $this->pageRepo->persist($fromPage);
        }

        $toLink = $link->getAttribute('href');
        $urlData = parse_url($toLink);

        if (!isset($urlData['host'])) {
            $toSite = $fromSite;
            $isInternal = true;
        } else {
            $toSite = $this->siteRepo->ensureByUrl($urlData['host']);
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
            $isAlreadyExisting = $this->pageRepo->getBySiteAndUrl($toSite, $urlData['path']) !== null;
            $toPage = $this->pageRepo->ensureBySiteAndUrl($toSite, $urlData['path']);

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

            $this->linkRepo->persist($link);

            if (!$isAlreadyExisting) {
                $backlogItem = new Backlog('http://' . $toSite->getUrl() . $toPage->getUrl(), false);
                if ($this->shouldProcessItem($backlogItem)) {
                    $this->backlogRepo->persist($backlogItem);
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
    private function getPageContentsForItem(Backlog $item) : string
    {
        return $this->requester->getContents($item);
    }

    /**
     * Returns the title from the DOM page.
     *
     * @param string $content
     * @return string
     */
    private function getTitleFromContent($content) : string
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
    private function getLinksFromContent(string $content) : \DOMNodeList
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
    private function shouldProcessUrl(Site $fromSite, Site $toSite, string $fromUrl, string $toUrl) : bool
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
    private function shouldProcessItem(Backlog $item) : bool
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