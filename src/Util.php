<?php

namespace Scraper;

/**
 * Class Util
 * @package Scraper
 * @author Joost Mul <scraper@jmul.net>
 */
final class Util
{
    /**
     * Returns the requested value form the array with the given value as key. If the key is not set, the default will
     * be returned
     *
     * @param array       $array
     * @param mixed|array $values
     * @param mixed       $default
     * @return mixed
     */
    public static function arrayGet(array $array, $values, $default = null)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        $cur = $array;
        foreach ($values as $value) {
            if (isset($cur[$value])) {
                $cur = $cur[$value];
            } else {
                return $default;
            }
        }

        return $cur;
    }
} 
