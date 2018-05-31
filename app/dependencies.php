<?php
/**
 * Application Dependencies
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App;

use Slim\Container;
use Aura\Sql\ExtendedPdoInterface;
use Hashids\HashGenerator;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use Aura\SqlQuery\QueryFactory;
use Slim\Views\Twig;
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

// Add a PSR-3 compatible logger to the container.
$container[LoggerInterface::class] = function (Container $container) {
    // Create new monolog logger.
    $logger = new \Monolog\Logger('shortener');

    // If a log file was specified, add handler for that file to logger.
    if ($container['settings']['log']) {
        // Create stream handler for the specified log path.
        $handler = new \Monolog\Handler\StreamHandler(
            $container['settings']['log']
        );

        // Format the handler to only include stacktraces if in debug mode.
        $formatter = new \Monolog\Formatter\LineFormatter();
        $formatter->includeStacktraces($container['settings']['debug']);
        $handler->setFormatter($formatter);

        // Add web information to handler, and add handler to logger.
        $handler->pushProcessor(new \Monolog\Processor\WebProcessor());
        $logger->pushHandler($handler);
    }

    return $logger;
};

// Add a Query Factory to the container.
$container[QueryFactory::class] = function (Container $container) {
    return new QueryFactory($container['settings']['db']['type']);
};

// Add a Twig template processor to the container.
$container[Twig::class] = function (Container $container) {
    // Always search package's template directory.
    $paths = [dirname(__DIR__) . '/templates'];

    // If another template directory is specified, search it first.
    if (!empty($container['settings']['template']['path'])) {
        array_unshift($paths, $container['settings']['template']['path']);
    }

    // Define options for Twig.
    $options = [
        'cache' => dirname(__DIR__) . '/cache',
        'debug' => $container['settings']['debug']
    ];

    // Create Twig view and make package settings available.
    $view = new Twig($paths, $options);
    $view['settings'] = $container['settings']->all();

    // Add Aura.Html helper to the view.
    $helperLocatorFactory = new \Aura\Html\HelperLocatorFactory();
    $view['helper'] = $helperLocatorFactory->newInstance();

    // Add Slim extension to the Twig view.
    $basePath = $container['request']->getUri()->getBasePath();

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        rtrim(str_ireplace('index.php', '', $basePath), '/')
    ));

    return $view;
};

// Add a Url normalizer to the container.
$container[UrlInterface::class] = function (Container $container) {
    return new Url(
        $container[ExtendedPdoInterface::class],
        $container[QueryFactory::class],
        $container['settings']['db']['prefix']
    );
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

// Add the form action to the container.
$container[Action\FormAction::class] = function (Container $container) {
    return new Action\FormAction($container[Twig::class]);
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
        $container[HashGenerator::class],
        $container[ImageManager::class],
        $container[UrlInterface::class],
        $container[WatermarkInterface::class],
        $container['settings']['basepath'],
        $container['settings']['watermark']['limit']
    );
};

// Add our application's error handler to container.
$container['errorHandler'] = function (Container $container) {
    return new Handler\ErrorHandler(
        $container[LoggerInterface::class],
        $container[Twig::class],
        $container['settings']['debug']
    );
};

// Add our application's not found handler to container.
$container['notFoundHandler'] = function (Container $container) {
    return new Handler\NotFoundHandler($container[Twig::class]);
};

// Add our application's method not allowed handler to container.
$container['notAllowedHandler'] = function (Container $container) {
    return new Handler\NotAllowedHandler($container[Twig::class]);
};
