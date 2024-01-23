<?php

declare(strict_types=1);

namespace Lits\Watermark;

use Intervention\Image\Interfaces\ImageInterface as Image;
use Lits\Watermark;

final class OutsideWatermark extends Watermark
{
    use WideWatermarkTrait {
        adjust as protected adjustWide;
    }

    protected function adjust(Image $image): void
    {
        if (\is_null($this->logo)) {
            return;
        }

        $this->adjustWide($image);

        if ($this->default) {
            return;
        }

        $image->resizeCanvasRelative(
            0,
            $this->height,
            'rgb(51, 51, 51)',
            $this->config->top ? 'bottom' : 'top',
        );
    }

    protected function back(Image $image): void
    {
        if ($this->default) {
            parent::back($image);
        }
    }
}
