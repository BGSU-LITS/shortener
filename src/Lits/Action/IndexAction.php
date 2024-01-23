<?php

declare(strict_types=1);

namespace Lits\Action;

use Lits\Config\ShortenerConfig;
use Lits\Data\LinkData;
use Lits\Exception\InvalidDataException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;

final class IndexAction extends DatabaseAction
{
    /**
     * @throws HttpInternalServerErrorException
     * @throws HttpNotFoundException
     */
    public function action(): void
    {
        $redirect = null;

        if (isset($this->data['hash'])) {
            try {
                $link = LinkData::fromHash(
                    $this->data['hash'],
                    $this->settings,
                    $this->database,
                );
            } catch (InvalidDataException $exception) {
                throw new HttpNotFoundException(
                    $this->request,
                    'Not found',
                    $exception,
                );
            }

            $redirect = $link->link;
        }

        if (!\is_string($redirect)) {
            \assert($this->settings['shortener'] instanceof ShortenerConfig);

            $redirect = $this->settings['shortener']->redirect;
        }

        if (!\is_string($redirect)) {
            throw new HttpNotFoundException($this->request);
        }

        $this->redirect($redirect);
    }
}
