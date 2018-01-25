<?php

namespace Scraper\Repository;

use Scraper\Database\Database;
use Scraper\Data;

/**
 * Class Link
 * @package Scraper\Repository
 * @author Joost Mul <scraper@jmul.net>
 */
final class Link
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
     * @param Data\Link $link
     */
    public function persist(Data\Link $link)
    {
        $this->database->saveLink($link);
    }
}