<?php

declare(strict_types=1);

namespace Lits\Action;

use Lits\Config\ShortenerConfig;
use Lits\Config\WatermarkConfig;
use Lits\Data\LinkData;
use Lits\Watermark;
use Slim\Exception\HttpNotFoundException;

final class WatermarkAction extends DatabaseAction
{
    /** @throws HttpNotFoundException */
    public function action(): void
    {
        $path = $this->path();
        $text = $this->text();

        try {
            \assert($this->settings['watermark'] instanceof WatermarkConfig);

            $watermark = Watermark::fromConfig($this->settings['watermark']);
            $encoded = $watermark->apply($path, $text);

            $this->response = $this->response->withHeader(
                'Content-Type',
                $encoded->mediaType(),
            );

            $this->response->getBody()->write($encoded->toString());
        } catch (\Throwable $exception) {
            throw new HttpNotFoundException(
                $this->request,
                null,
                $exception,
            );
        }
    }

    /** @throws HttpNotFoundException */
    private function text(): string
    {
        $text = $this->request->getUri()->getHost();

        if (!isset($_SERVER['HTTP_REFERER'])) {
            return $text;
        }

        try {
            $link = LinkData::fromLink(
                $_SERVER['HTTP_REFERER'],
                $this->settings,
                $this->database,
            );

            return $text . '/' . $link->hash();
        } catch (\Throwable $exception) {
            throw new HttpNotFoundException(
                $this->request,
                null,
                $exception,
            );
        }
    }

    /** @throws HttpNotFoundException */
    private function path(): string
    {
        \assert($this->settings['shortener'] instanceof ShortenerConfig);

        if (!isset($this->data['path']) || $this->data['path'] === '') {
            throw new HttpNotFoundException($this->request);
        }

        $path = \ltrim($this->data['path'], \DIRECTORY_SEPARATOR);
        $root = $this->settings['shortener']->root;

        if (\is_string($root)) {
            $path = \rtrim($root, \DIRECTORY_SEPARATOR) .
                \DIRECTORY_SEPARATOR . $path;
        }

        if (!\file_exists($path)) {
            throw new HttpNotFoundException($this->request);
        }

        return $path;
    }
}
