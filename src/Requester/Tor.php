<?php

namespace Scraper\Requester;

use Scraper\Data\Backlog;
use Scraper\Logger\Logger;
use Scraper\Util;

/**
 * Class Tor
 * @package Scraper\Requester
 * @author Joost Mul <scraper@jmul.net>
 */
final class Tor implements Requester
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
     *
     * @param Logger   $logger
     * @param array    $settings
     */
    public function __construct(Logger $logger, array $settings = [])
    {
        $this->logger = $logger;
        $this->torPort = \Scraper\Util::arrayGet($settings, ['port'], self::DEFAULT_PORT);
        $this->forceNewIdentityEveryRequest = Util::arrayGet($settings, ['forceNewIdentity'], false);
    }

    /**
     * @param string $url
     * @return \resource
     */
    private function getCurl(string $url)
    {
        $torSocks5Proxy = "socks5://localhost:" . $this->torPort;
        $ch = curl_init();

        
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, $torSocks5Proxy);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);

        return $ch;
    }

    /**
     * @param resource $ch
     * @return mixed
     */
    private function execCloseAndReturnCurl($ch)
    {
        $result = curl_exec($ch);

        if (!$result) {
            $this->logger->log(Logger::TAG_ERRO, "CURL error " . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @param Backlog $item
     * @return string
     */
    public function getContents(Backlog $item) : string
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
        //exec("killall -HUP tor");
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return self::NAME;
    }
}