<?php
/**
 * Application Routes
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Action;

// Default redirect if no path specified.
$app->get('/', IndexAction::class);

// Path appears to be a hash specifying a link to redirect to.
$app->get('/{hash:[A-Za-z0-9]+}', RedirectAction::class);

// Path appears to be an image that may be watermarked.
$app->get('{path:.*(?:gif|jpe?g|png)}', WatermarkAction::class);
