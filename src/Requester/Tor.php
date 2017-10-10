<?php

namespace Scraper\Requester;

use Scraper\Data\Backlog;
use Scraper\Logger\Logger;
use Scraper\Util;

/**
 * Class Client
 */
class Tor implements Requester
{
    const NAME = 'TOR';

    /**
     * int
     */
    const DEFAULT_PORT = 9050;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $torPort = self::DEFAULT_PORT;

    /**
     * @var bool
     */
    private $forceNewIdentityEveryRequest = false;

    /**
     * Tor constructor.
     * @param Logger   $logger
     * @param array             $settings
     */
    public function __construct(Logger $logger, $settings = [])
    {
        $this->logger = $logger;
        $this->torPort = \Scraper\Util::arrayGet($settings, ['port'], self::DEFAULT_PORT);
        $this->forceNewIdentityEveryRequest = Util::arrayGet($settings, ['forceNewIdentity'], false);
    }

    /**
     * @return resource
     */
    private function getCurl($url)
    {
        $torSocks5Proxy = "socks5://localhost:" . $this->torPort;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, $torSocks5Proxy);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        return $ch;
    }

    /**
     * @param resource $ch
     * @return mixed
     */
    private function execCloseAndReturnCurl($ch)
    {
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @param Backlog $item
     * @return string
     */
    public function getContents(Backlog $item)
    {
        $result = $this->execCloseAndReturnCurl(
            $this->getCurl($item->getLink())
        );

        if ($this->forceNewIdentityEveryRequest == true) {
            $this->logger->log(Logger::TAG_INFO, "Requesting new TOR identity");
            $this->forceNewIdentity();
            $this->logger->log(Logger::TAG_SUCC, "Now TOR identity gained");
        }

        return $result;
    }

    /**
     *
     */
    public function forceNewIdentity()
    {
        exec("killall -HUP tor");
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }
}