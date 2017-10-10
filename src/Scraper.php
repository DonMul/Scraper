<?php

namespace Scraper;
use Scraper\Data;
use Scraper\Logger;
use Scraper\Database;
use Scraper\Requester;

/**
 * Class Scraper
 * @author Joost Mul <scraper@jmul.net>
 */
final class Scraper
{
    /**
     * @var \Scraper\Database\MySQL
     */
    private $database;

    /**
     * @var \Scraper\Processor
     */
    private $processor;

    /**
     * @var \Scraper\Requester\Requester
     */
    private $requester;

    /**
     * @var Logger\Logger $logger
     */
    private $logger;

    /**
     * Scraper constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->logger = Logger\Factory::getLogger(Util::arrayGet($settings, 'logger'));

        $this->database = Database\Factory::getDatabase(
            Util::arrayGet($settings, ['database', 'engine']),
            Util::arrayGet($settings, ['database'], [])
        );

        $this->requester = Requester\Factory::getRequester(
            Util::arrayGet($settings, ['requester', 'type'], Requester\Curl::getName()),
            $this->logger,
            Util::arrayGet($settings, ['requester'])
        );

        $this->processor = new Processor($this->database, $this->logger, $this->requester);

        $this->logger->log(Logger\Logger::TAG_INFO, "Scraper initialised: DB={$this->database->getName()} Requester={$this->requester->getName()}");
    }

    /**
     *
     */
    public function run()
    {
        $this->logger->log(Logger\Logger::TAG_SUCC, "Starting run");
        while (true) {
            while(($backlogItem = Data\Backlog::getNotLockedBacklogItem($this->database)) !== null) {
                $this->logger->log(Logger\Logger::TAG_INFO, "Start {$backlogItem->getLink()}");

                $isLocked = $backlogItem->ensureLocked($this->database);
                if (!$isLocked) {
                    $this->logger->log(Logger\Logger::TAG_WARN, "Can't obtain lock");
                    continue;
                }

                $this->logger->log(Logger\Logger::TAG_SUCC, "Obtained lock");
                try {
                    $this->processor->processBacklogItem($backlogItem);
                } catch (\Exception $ex) {
                    $this->logger->log(Logger\Logger::TAG_ERRO, $ex->getMessage());
                }
            }

            $this->logger->log(Logger\Logger::TAG_INFO, "Sleeping");
            sleep(5);
        }
    }
}
