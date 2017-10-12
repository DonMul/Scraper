<?php

namespace Scraper\Lock;

use Scraper\Database\Database;

class Normal implements Lock
{
    const NAME = 'Normal';

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function lock(Lockable $lockable, Database $database)
    {
        return $lockable->lock($database);
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function unlock(Lockable $lockable, Database $database)
    {
       return $lockable->unlock($database);
    }

    /**
     * @param Lockable $lockable
     * @param Database $database
     * @return bool
     */
    public function isLocked(Lockable $lockable, Database $database)
    {
        return $lockable->isLocked($database);
    }

}