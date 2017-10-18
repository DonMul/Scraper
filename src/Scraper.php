<?php

namespace Scraper;
use Scraper\Data;
use Scraper\Logger;
use Scraper\Database;
use Scraper\Cache;
use Scraper\Requester;

/**
 * Class Scraper
 * @author Joost Mul <scraper@jmul.net>
 */
final class Scraper
{
    /**
     * Database instance used to interact with a storage
     *
     * @var Database\Database
     */
    private $database;

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
     * Scraper constructor.
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        // Initialize the logger
        $this->logger = Logger\Factory::getLogger(Util::arrayGet($settings, 'logger'));

        // Initialize the Database connection
        $this->database = Database\Factory::getDatabase(
            Util::arrayGet($settings, ['database', 'engine']),
            Util::arrayGet($settings, ['database'], [])
        );

        // Initialize the requester
        $this->requester = Requester\Factory::getRequester(
            Util::arrayGet($settings, ['requester', 'type'], Requester\Curl::getName()),
            $this->logger,
            Util::arrayGet($settings, ['requester'])
        );

        // Initialize the processor
        $this->processor = new Processor(
            $this->database,
            $this->logger,
            $this->requester,
            Util::arrayGet($settings, ['engine'])
        );

        // Initialize the cache
        $this->cacher = Cache\Factory::getInstance(Util::arrayGet($settings, ['cache'], Cache\Memory::getName()));

        $this->logger->log(Logger\Logger::TAG_INFO, "Scraper initialised: DB={$this->database->getName()} Requester={$this->requester->getName()}");
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
            while (($backlogItem = Data\Backlog::getNotLockedBacklogItem($this->database, $backlogItem)) !== null) {
                $this->logger->log(Logger\Logger::TAG_INFO, "Start {$backlogItem->getLink()}");

                // Lock the backlog item
                $isLocked = $backlogItem->ensureLocked($this->database);

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
                } catch (\Throwable  $ex) {
                    $this->logger->log(Logger\Logger::TAG_ERRO, $ex->getMessage());
                }
            }

            $this->logger->log(Logger\Logger::TAG_INFO, "Sleeping");
            sleep(5);
        }
    }
}
