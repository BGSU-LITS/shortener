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

// Provide a form to upload an image at /w, and handle submission of that form.
$app->get('/w', FormAction::class);
$app->post('/w', WatermarkAction::class);

// Paths that begin with /w/ specify an image to be watermarked.
$app->get('/w{path:/.*\.(?:gif|jpe?g|png)}', WatermarkAction::class);

// Other paths appear to be a hash specifying a link to redirect to.
$app->get('/{hash:[A-Za-z0-9]+}', RedirectAction::class);
