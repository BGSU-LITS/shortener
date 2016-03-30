<?php
/**
 * Phinx Configuration
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

use \Aura\Sql\ExtendedPdoInterface;

// Load settings from the application
$settings = require 'app/settings.php';

// Return Phinx Configuration.
return [
    'paths' => [
        'migrations' =>
            '%%PHINX_CONFIG_DIR%%/migrations'
    ],

    'environments' => [
        'default_migration_table' =>
            $settings['db']['prefix'] . 'phinx',

        'default_database' =>
            'default',

        'default' => [
            'name' =>
                $settings['db']['name'],
            'connection' =>
                new \Pdo(
                    $settings['db']['dsn'],
                    $settings['db']['username'],
                    $settings['db']['password']
                )
        ]
    ]
];
