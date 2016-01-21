<?php
/**
 * Application Middleware
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

// Allow Whoops to be used for debugging.
$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);
