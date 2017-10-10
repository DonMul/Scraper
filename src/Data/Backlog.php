<?php

namespace Scraper\Data;

use Scraper\Database\MySQL;
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
     * @param MySQL $database
     * @return Backlog
     */
    public static function getNotLockedBacklogItem(MySQL $database)
    {
        $result = $database->fetchOne(
            "SELECT * FROM backlog WHERE islocked = 0 ORDER BY RAND() LIMIT 1 "
        );

        if ($result) {
            return self::convertToObject($result);
        }

        return null;
    }

    /**
     * @param MySQL $database
     * @return bool
     */
    public function ensureLocked(MySQL $database)
    {
        $result = $database->query(
            "UPDATE backlog SET isLocked = ? WHERE isLocked = ? AND uniqueHash = ?", [
                1,
                0,
                $this->getUniqueHash()
            ], 'iis'
        );

        if ($result->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param MySQL $database
     * @return bool
     */
    public function save(MySQL $database)
    {
        $result = $database->query("REPLACE INTO backlog (`link`, `isLocked`, `uniqueHash`) VALUES ( ?, ?, ? )", [
            $this->getLink(),
            intval($this->isIsLocked()),
            $this->getUniqueHash(),
        ], 'sis');

        return $result->affected_rows > 0;
    }

    /**
     * @param MySQL $database
     * @return boolean
     */
    public function delete(MySQL $database) {
        $result = $database->query("DELETE FROM backlog WHERE uniqueHash = ?", [$this->getUniqueHash()], 's');

        return $result->affected_rows > 0;
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public static function getAmount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM backlog");
        return $result['cnt'];
    }

    /**
     * @param array $data
     * @return Backlog
     */
    private static function convertToObject($data)
    {
        return new Backlog(
            \Scraper\Util::arrayGet($data, 'link'),
            \Scraper\Util::arrayGet($data, 'isLocked'),
            \Scraper\Util::arrayGet($data, 'uniqueHash')
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