<?php

namespace Scraper\Lock;

use Scraper\Database\Database;

/**
 * Interface Lockable
 * @package Scraper\Lock
 */
interface Lockable
{
    /**
     * @return bool
     */
    public function lock(Database $database) : bool;

    /**
     * @return bool
     */
    public function unlock(Database $database) : bool;

    /**
     * @return bool
     */
    public function isLocked(Database $database) : bool;
}