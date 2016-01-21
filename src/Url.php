<?php
/**
 * URL Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App;

/**
 * A class which allows for the normalization of a URL.
 */
class Url implements UrlInterface
{
    /**
     * Normalize a URL.
     * @param string $url The URL to be normalized.
     * @return string The normalized URL.
     */
    public function normalize($url)
    {
        $normalizer = new \URL\Normalizer($url, true, true);
        return $normalizer->normalize();
    }
}
