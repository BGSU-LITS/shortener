<?php
/**
 * Phinx Configuration
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

use \Aura\Sql\ExtendedPdoInterface;

// Load settings from the container.
$container = require 'app/container.php';

// Return Phinx Configuration.
return [
    'paths' => [
        'migrations' =>
            '%%PHINX_CONFIG_DIR%%/migrations'
    ],

    'environments' => [
        'default_migration_table' =>
            $container['settings']['db']['prefix']. 'phinx',

        'default_database' =>
            'default',

        'default' => [
            'name' =>
                $container['settings']['db']['name'],

            'connection' =>
                $container[ExtendedPdoInterface::class]
        ]
    ]
];
