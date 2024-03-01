<?php

declare(strict_types=1);

use Lits\ErrorHandler\ShortenerErrorHandler;
use Lits\ErrorRenderer\HtmlErrorRenderer;
use Lits\ErrorRenderer\PlainTextErrorRenderer;
use Lits\Framework;
use Slim\Middleware\ErrorMiddleware;

use function DI\autowire;
use function DI\get;

return function (Framework $framework): void {
    $framework->addDefinition(
        ErrorMiddleware::class,
        autowire()
            ->constructorParameter('displayErrorDetails', false)
            ->constructorParameter('logErrors', true)
            ->constructorParameter('logErrorDetails', true)
            ->method(
                'setDefaultErrorHandler',
                get(ShortenerErrorHandler::class),
            ),
    );

    $framework->addDefinition(
        ShortenerErrorHandler::class,
        autowire()
            ->method(
                'registerErrorRenderer',
                'text/html',
                HtmlErrorRenderer::class,
            )
            ->method(
                'registerErrorRenderer',
                'text/plain',
                PlainTextErrorRenderer::class,
            ),
    );
};
