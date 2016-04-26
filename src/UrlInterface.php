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
 * An interface used for normalizing and saving URLs.
 */
interface UrlInterface
{
    /**
     * Normalize a URL.
     * @param string $url The URL to be normalized.
     * @return string The normalized URL.
     */
    public function normalize($url);

    /**
     * Saves a URL to the database if it has not already been saved.
     * @param string $url The url that should be saved.
     * @return integer The ID of the row for the url in the database.
     */
    public function save($url);
}
