<?php

namespace Scraper\Lock;

use Scraper\Database\Database;

/**
 * Class Normal
 * @package Scraper\Lock
 * @author Joost Mul <scraper@jmul.net>
 */
final class Normal implements Lock
{
    const NAME = 'Normal';

    /**
     * @return string
     */
    public static function getName() : string
    {
        return self::NAME;
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function lock(Lockable $lockable, Database $database) : bool
    {
        return $lockable->lock($database);
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function unlock(Lockable $lockable, Database $database) : bool
    {
       return $lockable->unlock($database);
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function isLocked(Lockable $lockable, Database $database) : bool
    {
        return $lockable->isLocked($database);
    }

}