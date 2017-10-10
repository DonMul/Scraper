<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Autoload.php';

$settings = require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Settings.php';

$database = new \Scraper\Database\MySQL(
    \Scraper\Util::arrayGet($settings, ['database'], [])
);

$opts = getopt('s:c:e', ['site', 'command', 'extended']);

$command = \Scraper\Util::arrayGet($opts, 'c');
if (empty($command)) {
    $command = \Scraper\Util::arrayGet($opts, 'command');
}

switch ($command) {
    case 'stats':
        if (isset($opts['s']) || isset($opts['site'])) {
            $url = isset($opts['s']) ? $opts['s'] : $opts['site'];
            $site = \Scraper\Data\Site::getByUrl($url, $database);
            if (!$site) {
                echo "Site not found" . PHP_EOL;
            } else {
                echo "URL: \t" . $site->getUrl() . PHP_EOL;
                echo "Pages: \t" . $site->getPagesCount($database) . PHP_EOL;

                echo PHP_EOL . "==INCOMING==" . PHP_EOL;
                echo "Links: \t" . $site->getIncomingLinksCount($database) . PHP_EOL;
                echo "Sites: \t" . $site->getIncomingSitesCount($database) . PHP_EOL;

                echo PHP_EOL . "==OUTGOING==" . PHP_EOL;
                echo "Links: \t" . $site->getOutgoingLinksCount($database) . PHP_EOL;
                echo "Sites: \t" . $site->getOutgoingSitesCount($database) . PHP_EOL . PHP_EOL;
            }
        } else {
            echo "Backlog Items: \t\t" . \Scraper\Data\Backlog::getAmount($database) . PHP_EOL;
            echo "Links: \t\t\t" . \Scraper\Data\Link::getAmount($database) . PHP_EOL;
            echo "Pages: \t\t\t" . \Scraper\Data\Page::getAmount($database) . PHP_EOL;
            echo "Sites: \t\t\t" . \Scraper\Data\Site::getAmount($database) . PHP_EOL;

            if (isset($opts['e']) || isset($opts['extended'])) {
                $start = \Scraper\Data\Link::getAmount($database);
                $seconds = 5;
                sleep($seconds);
                echo "Avg. Links per second: \t" . (\Scraper\Data\Link::getAmount($database) - $start) / $seconds . PHP_EOL . PHP_EOL;
            }
        }

        break;
    case 'list':
        echo "Sites:" . PHP_EOL;
        foreach (\Scraper\Data\Site::getAllUrls($database) as $url) {
            echo $url . PHP_EOL;
        }
        break;
    default:
        echo "UNKNOWN COMMAND: {$command}" . PHP_EOL;
        break;
}