<?php

namespace Scraper;

use Scraper\Logger;
use Scraper\Database;
use Scraper\Cache;
use Scraper\Repository\Backlog;
use Scraper\Repository\Link;
use Scraper\Repository\Page;
use Scraper\Repository\Site;
use Scraper\Requester;

/**
 * Class Scraper
 * @package Scraper
 * @author Joost Mul <scraper@jmul.net>
 */
final class Scraper
{
    /**
     * Used to process the requests
     *
     * @var \Scraper\Processor
     */
    private $processor;

    /**
     * Requester which request the contents from the URLS
     *
     * @var Requester\Requester;
     */
    private $requester;

    /**
     * Logs possible information
     *
     * @var Logger\Logger $logger
     */
    private $logger;

    /**
     * Caches data, improve speed
     *
     * @var Cache\Cache
     */
    private $cacher;

    /**
     * @var Backlog
     */
    private $backlogRepo;

    /**
     * @var Link
     */
    private $linkRepo;

    /**
     * @var Page
     */
    private $pageRepo;

    /**
     * @var Site
     */
    private $siteRepo;

    /**
     * Scraper constructor.
     *
     * @param array $settings
     */
    public function __construct($settings, Backlog $backlogRepo = null, Link $linkRepo = null, Page $pageRepo = null, Site $siteRepo = null)
    {
        // Initialize the logger
        $this->logger = Logger\Factory::getLogger(Util::arrayGet($settings, 'logger'));

        // Initialize the Database connection
        $database = Database\Factory::getDatabase(
            Util::arrayGet($settings, ['database', 'engine']),
            Util::arrayGet($settings, ['database'], [])
        );

        if (!($backlogRepo instanceof Backlog)) {
            $backlogRepo = new Backlog($database);
        }
        $this->backlogRepo = $backlogRepo;

        if (!($linkRepo instanceof Link)) {
            $linkRepo = new Link($database);
        }
        $this->linkRepo = $linkRepo;

        if (!($pageRepo instanceof Page)) {
            $pageRepo = new Page($database);
        }
        $this->pageRepo = $pageRepo;

        if (!($siteRepo instanceof Site)) {
            $siteRepo = new Site($database);
        }
        $this->siteRepo = $siteRepo;

        // Initialize the requester
        $this->requester = Requester\Factory::getRequester(
            Util::arrayGet($settings, ['requester', 'type'], Requester\Curl::getName()),
            $this->logger,
            Util::arrayGet($settings, ['requester'])
        );

        // Initialize the processor
        $this->processor = new Processor(
            $this->logger,
            $this->requester,
            $this->backlogRepo,
            $this->linkRepo,
            $this->pageRepo,
            $this->siteRepo,
            Util::arrayGet($settings, ['engine'])
        );

        // Initialize the cache
        $this->cacher = Cache\Factory::getInstance(Util::arrayGet($settings, ['cache'], Cache\Memory::getName()));

        $this->logger->log(Logger\Logger::TAG_INFO, "Scraper initialised: DB={$database->getName()} Requester={$this->requester->getName()}");
    }

    /**
     * Process the entire available backlog
     */
    public function run()
    {
        $this->logger->log(Logger\Logger::TAG_SUCC, "Starting run");

        $backlogItem = null;
        // This loop should run forever and processes all backlogs
        while (true) {
            // While there are items in the backlog, they should be processed
            while (($backlogItem = $this->backlogRepo->getNotLockedBacklogItem($backlogItem)) !== null) {
                $this->logger->log(Logger\Logger::TAG_INFO, "Start {$backlogItem->getLink()}");

                // Lock the backlog item
                $isLocked = $this->backlogRepo->ensureLocked($backlogItem);

                // If the Backlog item can't be locked, we should skip it. The reason it can't be locked is most likely
                // because another instance of Scraper is running
                if (!$isLocked) {
                    $this->logger->log(Logger\Logger::TAG_WARN, "Can't obtain lock");
                    continue;
                }

                $this->logger->log(Logger\Logger::TAG_SUCC, "Obtained lock");

                // Process the backlog item
                try {
                    $this->processor->processBacklogItem($backlogItem);
                } catch (\Throwable $ex) {
                    $this->logger->log(Logger\Logger::TAG_ERRO, $ex->getMessage());
                }
            }

            $this->logger->log(Logger\Logger::TAG_INFO, "Sleeping");
            sleep(5);
        }
    }
}
