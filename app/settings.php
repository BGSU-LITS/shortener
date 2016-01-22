<?php
/**
 * Application Settings
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

use M1\Env\Parser;

// Setup the default settings for the application.
$settings = [
    // Whether Whoops should be used for debugging.
    'debug' => false,

    // Base path of directory where shortener is installed.
    // Defaults to the current working directory.
    'basepath' => getcwd(),

    // Link to redirect to by default.
    'redirect' => false,

    // Database configuration.
    'db' => [
        // PDO connection DSN.
        'dsn' => false,

        // PDO connection username.
        'username' => false,

        // PDO connection password.
        'password' => false,

        // PDO connection type.
        'type' => false,

        // Database name.
        'name' => false,

        // Table prefix.
        'prefix' => false,
    ],

    // Watermark configuration.
    'watermark' => [
        // Watermark type, either default, inside or outside.
        'type' => false,

        // Full path to font file to be used for text added to the watermark.
        // Defaults to the bundled copy of Source Code Pro Bold.
        'font' => dirname(__DIR__) . '/fonts/SourceCodePro-Bold.ttf',

        // Full path to image file of a logo to be added to the watermark.
        'logo' => false,

        // Preferred width of the watermark.
        // May be increased.
        'width' => false,

        // Preferred height of the watermark.
        // May be increased.
        'height' => false,

        // Position of watermark within the image.
        // A combination of top or bottom and left or right.
        'position' => false,

        // Limit in pixels of the largest image to add a watermark to.
        'limit' => 5100 * 6600,
    ]
];

// Check if a .env file exists.
$file = dirname(__DIR__) . '/.env';

if (file_exists($file)) {
    // If so, load the settings from that file.
    $text = file_get_contents($file);

    // Parse settings into key/value pairs.
    foreach (Parser::parse($text) as $key => $value) {
        // If a value was specified, add it to the settings array.
        if (!empty($value)) {
            $target = &$settings;

            foreach (explode('_', strtolower($key)) as $part) {
                $target = &$target[$part];
            }

            $target = $value;
        }
    }
}

// Return the complete settings array.
return $settings;
