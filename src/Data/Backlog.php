<?php

namespace Scraper\Data;

use Scraper\Database\Database;
use Scraper\Lock\Lockable;
use Scraper\Util;

/**
 * Class Backlog
 * @author Joost Mul <scraper@jmul.net>
 */
final class Backlog
{
    /**
     * @var string
     */
    private $link;

    /**
     * @var boolean
     */
    private $isLocked;

    /**
     * @var string
     */
    private $uniqueHash;

    /**
     * Backlog constructor.
     * @param string $link
     * @param boolean$isLocked
     * @param string $uniqueHash
     */
    public function __construct($link, $isLocked, $uniqueHash = '')
    {
        $this->setLink($link);
        $this->setIsLocked(!!$isLocked);

        if (empty($uniqueHash)) {
            $uniqueHash = hash('sha512', $link);
        }

        $this->setUniqueHash($uniqueHash);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return strtolower($this->link);
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return boolean
     */
    public function isIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * @param boolean $isLocked
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
    }

    /**
     * @return string
     */
    public function getUniqueHash()
    {
        return $this->uniqueHash;
    }

    /**
     * @param string $uniqueHash
     */
    public function setUniqueHash($uniqueHash)
    {
        $this->uniqueHash = $uniqueHash;
    }

    /**
     * @param Database $database
     * @return Backlog
     */
    public static function getNotLockedBacklogItem(Database $database)
    {
        $result = $database->getRandomUnlockedBacklogItem();

        if ($result) {
            return self::convertToObject($result);
        }

        return null;
    }

    /**
     * @param Database $database
     * @return bool
     */
    public function ensureLocked(Database $database)
    {
        return $database->lockBacklogItem($this);
    }

    /**
     * @param Database $database
     * @return bool
     */
    public function save(Database $database)
    {
        return $database->saveBacklogItem($this);

    }

    /**
     * @param Database $database
     * @return boolean
     */
    public function delete(Database $database) {
        return $database->deleteBacklogItem($this);
    }

    /**
     * @param array $data
     * @return Backlog
     */
    private static function convertToObject($data)
    {
        return new Backlog(
            Util::arrayGet($data, 'link'),
            Util::arrayGet($data, 'isLocked'),
            Util::arrayGet($data, 'uniqueHash')
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $urlData = parse_url($this->getLink());
        return Util::arrayGet($urlData, ['host']);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $urlData = parse_url($this->getLink());
        return Util::arrayGet($urlData, ['path'], '/');
    }
}