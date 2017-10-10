<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Autoload.php';

$settings = require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Settings.php';
$scraper = new \Scraper\Scraper($settings);

$scraper->run();