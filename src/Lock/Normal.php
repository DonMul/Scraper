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

    public function lock(Lockable $lockable, Database $database)
    {
        return $lockable->lock($database);
    }

    public function unlock(Lockable $lockable, Database $database)
    {
       return $lockable->unlock($database);
    }

    public function isLocked(Lockable $lockable, Database $database)
    {
        return $lockable->isLocked($database);
    }

}