<?php

namespace Scraper\Data;

use Scraper\Database\MySQL;

/**
 * Class Link
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
    public function __construct($fromPageId, $toPageId, $url, $text, $raw, $isInternal)
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
    public function getFromPageId()
    {
        return $this->fromPageId;
    }

    /**
     * @param int $fromPageId
     */
    public function setFromPageId($fromPageId)
    {
        $this->fromPageId = $fromPageId;
    }

    /**
     * @return int
     */
    public function getToPageId()
    {
        return $this->toPageId;
    }

    /**
     * @param int $toPageId
     */
    public function setToPageId($toPageId)
    {
        $this->toPageId = $toPageId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param string $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return int
     */
    public function getIsInternal()
    {
        return $this->isInternal;
    }

    /**
     * @param int $isInternal
     */
    public function setIsInternal($isInternal)
    {
        $this->isInternal = $isInternal;
    }

    /**
     * @param MySQL $database
     * @return int
     */
    public static function getAmount(MySQL $database)
    {
        $result = $database->fetchOne("SELECT count(1) AS cnt FROM link");
        return $result['cnt'];
    }

    /**
     * @param MySQL $database
     * @return bool
     */
    public function save(MySQL $database)
    {
        $result = $database->query("INSERT INTO link (`fromPageId`, `toPageId`, `url`, `text`, `raw`, `isInternal`) VALUES ( ?, ?, ?, ?, ?, ? )", [
            $this->getFromPageId(),
            $this->getToPageId(),
            $this->getUrl(),
            $this->getText(),
            $this->getRaw(),
            intval($this->getIsInternal())
        ], 'iisssi');

        return $result->affected_rows > 0;
    }
}