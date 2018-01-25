<?php

namespace Scraper\Data;

use Scraper\Util;

/**
 * Class Backlog
 * @package Scraper\Data
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
     * @param string  $link
     * @param boolean $isLocked
     * @param string  $uniqueHash
     */
    public function __construct(string $link, bool $isLocked, string $uniqueHash = '')
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
    public function getLink() : string
    {
        return strtolower($this->link);
    }

    /**
     * @param string $link
     */
    public function setLink(string $link)
    {
        $this->link = $link;
    }

    /**
     * @return bool
     */
    public function isIsLocked() : bool
    {
        return $this->isLocked;
    }

    /**
     * @param boolean $isLocked
     */
    public function setIsLocked(bool $isLocked)
    {
        $this->isLocked = $isLocked;
    }

    /**
     * @return string
     */
    public function getUniqueHash() : string
    {
        return $this->uniqueHash;
    }

    /**
     * @param string $uniqueHash
     */
    public function setUniqueHash(string $uniqueHash)
    {
        $this->uniqueHash = $uniqueHash;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        $urlData = parse_url($this->getLink());
        return Util::arrayGet($urlData, ['host']);
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        $urlData = parse_url($this->getLink());
        return Util::arrayGet($urlData, ['path'], '/');
    }
}