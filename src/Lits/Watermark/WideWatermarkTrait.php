<?php

declare(strict_types=1);

namespace Lits\Watermark;

use Intervention\Image\Interfaces\ImageInterface as Image;
use Intervention\Image\Typography\FontFactory as Font;

trait WideWatermarkTrait
{
    protected bool $default = false;

    protected function adjust(Image $image): void
    {
        if (\is_null($this->logo)) {
            return;
        }

        $width = $this->logo->width() + 10;
        $height = $this->logo->height() + 20;

        if ($this->width + $width >= $image->width()) {
            $this->default = true;

            parent::adjust($image);

            return;
        }

        $this->width = $image->width();

        if ($height > $this->height) {
            $this->height = $height;
        }
    }

    protected function text(Image $image, string $text): void
    {
        if ($this->default) {
            parent::text($image, $text);

            return;
        }

        $image->text(
            $text,
            $image->width() - 10,
            self::position(
                0,
                $image->height(),
                (int) ($this->height / 2),
                $this->config->top,
            ),
            function (Font $font): void {
                $font->align('right');
                $font->color('#ffffff');
                $font->filename($this->config->font);
                $font->size(16);
                $font->valign('center');
            },
        );
    }

    protected function x(Image $image): int
    {
        if ($this->default) {
            return parent::x($image);
        }

        return 0;
    }

    protected function y(Image $image): int
    {
        if ($this->default) {
            return parent::y($image);
        }

        return self::position(
            $this->height,
            $image->height(),
            0,
            $this->config->top,
        );
    }
}
