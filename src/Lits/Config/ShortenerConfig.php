<?php

declare(strict_types=1);

namespace Lits\Config;

use Lits\Config;

final class ShortenerConfig extends Config
{
    /** @var list<string> $link_allow */
    public array $link_allow = [];

    /** @var list<string> $link_deny */
    public array $link_deny = [];

    public ?string $redirect = null;
    public ?string $root = null;

    public function __construct()
    {
        if (
            isset($_SERVER['DOCUMENT_ROOT']) &&
            \is_dir($_SERVER['DOCUMENT_ROOT'])
        ) {
            $this->root = $_SERVER['DOCUMENT_ROOT'];
        }
    }
}
