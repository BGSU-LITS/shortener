<?php

declare(strict_types=1);

use Lits\Config\ShortenerConfig;
use Lits\Config\TemplateConfig;
use Lits\Config\WatermarkConfig;
use Lits\Framework;

return function (Framework $framework): void {
    $framework->addConfig('shortener', new ShortenerConfig());
    $framework->addConfig('watermark', new WatermarkConfig());

    $settings = $framework->settings();
    assert($settings['template'] instanceof TemplateConfig);

    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    $settings['template']->paths[] = $path . 'template';

    $path .= 'settings.php';

    if (!file_exists($path)) {
        return;
    }

    $result = require $path;

    if (is_null($result)) {
        return;
    }

    assert(is_callable($result));
    $result($framework);
};
