<?php

declare(strict_types=1);

namespace Lits\ErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface as CallableResolver;

/** @psalm-suppress PropertyNotSetInConstructor */
final class ShortenerErrorHandler extends ErrorHandler
{
    public function __construct(
        CallableResolver $callableResolver,
        ResponseFactory $responseFactory,
        Logger $logger,
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);

        /** @psalm-suppress UnusedClosureParam */
        $this->logErrorRenderer = fn (
            \Throwable $exception,
            bool $displayErrorDetails,
        ): string => (string) $exception;
    }

    protected function writeToErrorLog(): void
    {
        if ($this->exception instanceof HttpNotFoundException) {
            return;
        }

        $renderer = $this->callableResolver->resolve($this->logErrorRenderer);
        $error = (string) $renderer($this->exception, $this->logErrorDetails);
        $this->logError($error);
    }
}
