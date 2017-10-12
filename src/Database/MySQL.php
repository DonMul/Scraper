<?php

namespace Scraper\Database;
use Scraper\Data\Backlog;
use Scraper\Data\Link;
use Scraper\Data\Page;
use Scraper\Data\Site;
use Scraper\Util;

/**
 * Class Database
 * @package Database
 * @author Joost Mul <scraper@jmul.net>
 */
final class MySQL implements Database
{
    const NAME = 'MySQL';

    /**
     * THe underlying MySQL database connection
     *
     * @var \mysqli
     */
    private $connection;

    /**
     * Whether or not the connection should have been initiated
     * @var bool
     */
    private $connectionLoaded = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $host;

    /**
     * MySQL constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->username = Util::arrayGet($settings, 'username', '');
        $this->password = Util::arrayGet($settings, 'password', '');
        $this->database = Util::arrayGet($settings, 'database', '');
        $this->host     = Util::arrayGet($settings, 'host', '');
    }

    /**
     * Executes the given query and returns the mysql_result of it. Bind the given params to the query's
     * prepared statement
     *
     * @param  string $query
     * @param  array  $params
     * @param  string $types
     * @return \mysqli_stmt
     * @throws \Exception
     */
    private function query($query, $params = [], $types = '')
    {
        if (count($params) !== strlen($types)) {
            throw new \Exception("MySQL Error: Given parameter amount does not match the types");
        }

        $this->ensureConnection();

        $stmt = $this->connection->prepare($query);
        if (!empty($params)) {
            $args = [];
            array_unshift($params, $types);
            $count = count($params);

            for ($i = 0; $i < $count; $i++) {
                $args[$i] = & $params[$i];
            }

            if (!$stmt) {
                throw new \Exception($this->connection->error);
            }
            call_user_func_array([$stmt, 'bind_param'], $args);
        }

        if (!$stmt) {
            throw new \Exception($this->connection->error);
        }

        $stmt->execute();
        if (!$stmt) {
            throw new \Exception($stmt->error, $stmt->errno);
        }

        return $stmt;
    }


    /**
     * Returns the first row of the given query's result set.
     *
     * @param string $query
     * @param array  $params
     * @param string $types
     * @return array
     */
    private function fetchOne($query, $params = [], $types = '')
    {
        $result = $this->query($query, $params, $types);
        $result = $result->get_result();
        $return = null;

        while ($row = $result->fetch_assoc()) {
            $return = $row;
            break;
        }

        return $return;
    }


    /**
     * Executes the query and returns its result set as an array with associative arrays
     *
     * @param string $query
     * @param array  $params
     * @param string $types
     * @return mixed
     */
    private function fetchAll($query, $params = [], $types = '')
    {
        $result = $this->query($query, $params, $types);
        $result = $result->get_result();
        $return = [];
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Makes sure the connection is made with the database.
     *
     * @throws \Exception
     */
    private function ensureConnection()
    {
        if (!$this->connectionLoaded) {
            $this->connection = mysqli_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );


            if (!$this->connection) {
                throw new \Exception("MySql connect error: " . mysqli_connect_error());
            }

            $this->connectionLoaded = true;
        }
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }

    /**
     * @return array
     */
    public function getRandomUnlockedBacklogItem()
    {
        return $this->fetchOne(
            "SELECT * FROM backlog WHERE islocked = 0 ORDER BY RAND() LIMIT 1 "
        );
    }

    /**
     * @param Backlog $item
     * @return bool
     */
    public function lockBacklogItem(Backlog $item)
    {
        $result = $this->query(
            "UPDATE backlog SET isLocked = ? WHERE isLocked = ? AND uniqueHash = ?", [
            1,
            0,
            $item->getUniqueHash()
        ], 'iis'
        );

        return $result->affected_rows > 0;
    }

    /**
     * @param Backlog $item
     * @return bool
     */
    public function saveBacklogItem(Backlog $item)
    {
        $result = $this->query("INSERT INTO backlog (`link`, `isLocked`, `uniqueHash`) VALUES ( ?, ?, ? )", [
            $item->getLink(),
            intval($item->isIsLocked()),
            $item->getUniqueHash(),
        ], 'sis');

        return $result->affected_rows > 0;
    }

    /**
     * @param Backlog $item
     * @return bool
     */
    public function deleteBacklogItem(Backlog $item)
    {
        $result = $this->query("DELETE FROM backlog WHERE uniqueHash = ?", [$item->getUniqueHash()], 's');
        return $result->affected_rows > 0;
    }

    /**
     * @param Link $link
     * @return bool
     */
    public function saveLink(Link $link)
    {
        $result = $this->query("INSERT INTO link (`fromPageId`, `toPageId`, `url`, `text`, `raw`, `isInternal`) VALUES ( ?, ?, ?, ?, ?, ? )", [
            $link->getFromPageId(),
            $link->getToPageId(),
            $link->getUrl(),
            $link->getText(),
            $link->getRaw(),
            intval($link->getIsInternal())
        ], 'iisssi');

        return $result->affected_rows > 0;
    }

    /**
     * @param Site $site
     * @param $url
     * @return array
     */
    public function getSiteBySiteAndUrl(Site $site, $url)
    {
        return $this->fetchOne("SELECT * FROM page WHERE siteId = ? AND url = ?", [
            $site->getId(),
            $url
        ], 'is');
    }

    /**
     * @param Page $page
     * @return Page
     */
    public function createPage(Page $page)
    {
        $result = $this->query("INSERT INTO page (`title`, `url`, `siteId`) VALUES ( ?, ?, ?)", [
            $page->getTitle(),
            $page->getUrl(),
            $page->getSiteId()
        ], 'ssi');

        $page->setId($result->insert_id);
        return $page;
    }

    /**
     * @param Page $page
     */
    public function updatePage(Page $page)
    {
        $this->query("REPLACE INTO page (`id`, `title`, `url`, `siteId`) VALUES ( ?, ?, ?, ?)", [
            $page->getId(),
            $page->getTitle(),
            $page->getUrl(),
            $page->getSiteId()
        ], 'issi');
    }

    /**
     * @param string $url
     * @return array
     */
    public function getSiteByUrl($url)
    {
        return $this->fetchOne("SELECT * FROM site WHERE url = ? LIMIT 1", [$url] , 's');
    }

    /**
     * @param Site $site
     * @return Site
     */
    public function createSite(Site $site)
    {
        $result = $this->query("INSERT INTO site (`url`) VALUES ( ? )", [$site->getUrl()], 's');
        $site->setId($result->insert_id);

        return $site;
    }

    /**
     * @param Site $site
     */
    public function updateSite(Site $site)
    {
        $this->query("REPLACE INTO site (`id`, `url`) VALUES ( ?, ? )", [
            $site->getId(),
            $site->getUrl()
        ], 'is');
    }
}
