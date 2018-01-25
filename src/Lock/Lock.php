<?php

namespace Scraper\Lock;

use Scraper\Database\Database;

/**
 * Interface Lock
 * @package Scraper\Lock
 * @author Joost Mul <scraper@jmul.net>
 */
interface Lock
{
    /**
     * @return string
     */
    public static function getName();

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function lock(Lockable $lockable, Database $database) : bool;

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function unlock(Lockable $lockable, Database $database) : bool;

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function isLocked(Lockable $lockable, Database $database) : bool;
}