<?php
/**
 * URL Interface
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App;

/**
 * An interface used for normalizing a URL.
 */
interface UrlInterface
{
    /**
     * Normalize a URL.
     * @param string $url The URL to be normalized.
     * @return string The normalized URL.
     */
    public function normalize($url);
}
