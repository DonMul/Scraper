<?php

// Include autoloading when not running in any other context
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Autoload.php';

// Include settings file
$settings = require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Settings.php';

// Run the scraper
$scraper = new \Scraper\Scraper($settings);
$scraper->run();