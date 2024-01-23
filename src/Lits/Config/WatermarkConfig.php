<?php

declare(strict_types=1);

namespace Lits\Config;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Interfaces\DriverInterface as Driver;
use Lits\Config;
use Lits\Watermark;
use Lits\Watermark\DefaultWatermark;

final class WatermarkConfig extends Config
{
    /** @var class-string|Driver $driver */
    public string|Driver $driver = GdDriver::class;

    /** @var class-string|Watermark $watermark */
    public string|Watermark $type = DefaultWatermark::class;

    public string $font = '5';
    public ?string $logo = null;

    public int $width = 250;
    public int $height = 35;

    public bool $left = false;
    public bool $top = false;
}
