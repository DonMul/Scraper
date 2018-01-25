<?php

namespace Scraper\Repository;

use Scraper\Database\Database;
use Scraper\Data;
use Scraper\Util;

/**
 * Class Backlog
 * @package Scraper\Repository
 * @author Joost Mul <scraper@jmul.net>
 */
final class Backlog
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
     * @param Data\Backlog $previousItem
     * @return Data\Backlog
     */
    public function getNotLockedBacklogItem(Data\Backlog $previousItem = null) : Data\Backlog
    {
        $path = '';
        if ($previousItem !== null) {
            $data = parse_url($previousItem->getUrl());
            if (isset($data['path'])) {
                $path = $data['path'];
            }
        }

        $result = $this->database->getRandomUnlockedBacklogItem($path);

        if ($result) {
            return $this->convertToObject($result);
        }

        return null;
    }

    /**
     * @param Data\Backlog $backlogItem
     * @return bool
     */
    public function ensureLocked(Data\Backlog $backlogItem) : bool
    {
        return $this->database->lockBacklogItem($backlogItem);
    }

    /**
     * @param Data\Backlog $item
     */
    public function persist(Data\Backlog $item)
    {
        $this->database->saveBacklogItem($item);
    }

    /**
     * @param array $data
     * @return Data\Backlog
     */
    private function convertToObject($data) : Data\Backlog
    {
        return new Data\Backlog(
            Util::arrayGet($data, 'link'),
            Util::arrayGet($data, 'isLocked'),
            Util::arrayGet($data, 'uniqueHash')
        );
    }
}