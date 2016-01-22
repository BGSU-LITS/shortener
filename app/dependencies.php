<?php
/**
 * Application Dependencies
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App;

use \Slim\Container;
use \Aura\Sql\ExtendedPdoInterface;
use \Hashids\HashGenerator;
use \Intervention\Image\ImageManager;
use \Aura\SqlQuery\QueryFactory;
use Watermark\WatermarkInterface;

// Add an Extended PDO database connection to the container.
$container[ExtendedPdoInterface::class] = function (Container $container) {
    return new \Aura\Sql\ExtendedPdo(
        $container['settings']['db']['dsn'],
        $container['settings']['db']['username'],
        $container['settings']['db']['password']
    );
};

// Add a Hash Generator to the container.
$container[HashGenerator::class] = function () {
    return new \Hashids\Hashids;
};

// Add an Intervention Image Manager to the container.
$container[ImageManager::class] = function () {
    return new ImageManager;
};

// Add a Query Factory to the container.
$container[QueryFactory::class] = function (Container $container) {
    return new QueryFactory($container['settings']['db']['type']);
};

// Add a Url normalizer to the container.
$container[UrlInterface::class] = function () {
    return new Url;
};

// Add a Watermark applyer to the container.
$container[WatermarkInterface::class] = function (Container $container) {
    // Make sure the type is either inside, outside or default.
    $type = strtolower($container['settings']['watermark']['type']);

    if (!in_array($type, ['inside', 'outside'])) {
        $type = 'default';
    }

    // Convert the type to a class name.
    $class = 'App\Watermark\\' . ucwords($type) . 'Watermark';

    // Return a new watermark class.
    return new $class(
        $container['settings']['watermark']['font'],
        $container['settings']['watermark']['logo'],
        $container['settings']['watermark']['width'],
        $container['settings']['watermark']['height'],
        $container['settings']['watermark']['position']
    );
};

// Add the index action to the container.
$container[Action\IndexAction::class] = function (Container $container) {
    return new Action\IndexAction($container['settings']['redirect']);
};

// Add the redirect action to the container.
$container[Action\RedirectAction::class] = function (Container $container) {
    return new Action\RedirectAction(
        $container[ExtendedPdoInterface::class],
        $container[HashGenerator::class],
        $container[QueryFactory::class],
        $container['settings']['db']['prefix']
    );
};

// Add the watermark action to the container.
$container[Action\WatermarkAction::class] = function (Container $container) {
    return new Action\WatermarkAction(
        $container[ExtendedPdoInterface::class],
        $container[HashGenerator::class],
        $container[ImageManager::class],
        $container[QueryFactory::class],
        $container[UrlInterface::class],
        $container[WatermarkInterface::class],
        $container['settings']['basepath'],
        $container['settings']['watermark']['limit'],
        $container['settings']['db']['prefix']
    );
};
