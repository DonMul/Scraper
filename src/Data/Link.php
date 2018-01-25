<?php

namespace Scraper\Data;

/**
 * Class Link
 * @package Scraper\Data
 * @author Joost Mul <scraper@jmul.net>
 */
final class Link
{
    /**
     * @var int
     */
    private $fromPageId;

    /**
     * @var int
     */
    private $toPageId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $raw;

    /**
     * @var int
     */
    private $isInternal;

    /**
     * Link constructor.
     * @param int     $fromPageId
     * @param int     $toPageId
     * @param string  $url
     * @param string  $text
     * @param string  $raw
     * @param boolean $isInternal
     */
    public function __construct(int $fromPageId, int $toPageId, string $url, string $text, string $raw, bool $isInternal)
    {
        $this->setFromPageId($fromPageId);
        $this->setToPageId($toPageId);
        $this->setUrl($url);
        $this->setText($text);
        $this->setRaw($raw);
        $this->setIsInternal(!!$isInternal);
    }

    /**
     * @return int
     */
    public function getFromPageId() : int
    {
        return $this->fromPageId;
    }

    /**
     * @param int $fromPageId
     */
    public function setFromPageId(int $fromPageId)
    {
        $this->fromPageId = $fromPageId;
    }

    /**
     * @return int
     */
    public function getToPageId() : int
    {
        return $this->toPageId;
    }

    /**
     * @param int $toPageId
     */
    public function setToPageId(int $toPageId)
    {
        $this->toPageId = $toPageId;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getRaw() : string
    {
        return $this->raw;
    }

    /**
     * @param string $raw
     */
    public function setRaw(string$raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return int
     */
    public function getIsInternal() : int
    {
        return $this->isInternal;
    }

    /**
     * @param int $isInternal
     */
    public function setIsInternal(int $isInternal)
    {
        $this->isInternal = $isInternal;
    }
}