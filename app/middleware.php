<?php
/**
 * Application Middleware
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

// Add handlers for sessions and CSRF protection in those sessions.
$app->add(new \Vperyod\SessionHandler\CsrfHandler);
$app->add(new \Vperyod\SessionHandler\SessionHandler);
