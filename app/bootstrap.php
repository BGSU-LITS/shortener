<?php
/**
 * Application Bootstrap
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

// Autoload dependencies.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Create new Slim container with included settings.
$container = new \Slim\Container(['settings' => require 'settings.php']);

// Create new Slim application with the container.
$app = new \Slim\App($container);

// Load application dependencies, middleware and routes.
require 'dependencies.php';
require 'middleware.php';
require 'routes.php';

// Run the application.
$app->run();
