<?php

declare(strict_types=1);

use Lits\Action\IndexAction;
use Lits\Action\WatermarkAction;
use Lits\Framework;

return function (Framework $framework): void {
    $framework->app()
        ->get('/w{path:/.*\.(?i)(?:gif|jpe?g|png)}', WatermarkAction::class);

    $framework->app()
        ->get('/[{hash:[A-Za-z0-9]+}]', IndexAction::class)
        ->setName('index');
};
