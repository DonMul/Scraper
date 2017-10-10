<?php

/**
 * Class Autoload
 * @author Joost Mul <scraper@jmul.net>
 */
final class Autoload
{
    /**
     * @param string $class
     */
    public function load($class)
    {
        $class = preg_replace('/^Scraper\\\/', '', $class);
        $fileName = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';

        if (file_exists($fileName)) {
            require_once $fileName;
        }
    }
}

spl_autoload_register([new Autoload(), 'load']);