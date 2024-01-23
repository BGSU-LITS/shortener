<?php

declare(strict_types=1);

namespace Lits;

use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Geometry\Factories\RectangleFactory as Rectangle;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface as EncodedImage;
use Intervention\Image\Interfaces\ImageInterface as Image;
use Intervention\Image\Typography\FontFactory as Font;
use Lits\Config\WatermarkConfig;
use Lits\Exception\InvalidConfigException;
use Lits\Watermark\DefaultWatermark;
use Lits\Watermark\InsideWatermark;
use Lits\Watermark\OutsideWatermark;

abstract class Watermark
{
    protected int $width;
    protected int $height;
    protected ImageManager $manager;
    protected ?Image $logo = null;

    public function __construct(protected WatermarkConfig $config)
    {
        $this->width = $config->width;
        $this->height = $config->height;
        $this->manager = ImageManager::withDriver($config->driver);

        if (\is_string($config->logo)) {
            $this->logo = $this->manager->read($config->logo);
        }
    }

    /** @throws InvalidConfigException */
    final public static function fromConfig(WatermarkConfig $config): self
    {
        if (\is_object($config->type)) {
            return $config->type;
        }

        return match ($config->type) {
            DefaultWatermark::class => new DefaultWatermark($config),
            InsideWatermark::class => new InsideWatermark($config),
            OutsideWatermark::class => new OutsideWatermark($config),
            default => throw new InvalidConfigException(\sprintf(
                'Could not load watermark type %s',
                $config->type,
            )),
        };
    }

    public function apply(string $path, string $text): EncodedImage
    {
        $image = $this->manager->read($path);
        $this->adjust($image);

        if ($this->fits($image)) {
            $this->back($image);
            $this->logo($image);
            $this->text($image, $text);
        }

        return $this->encode($image);
    }

    protected function adjust(Image $image): void
    {
        if (\is_null($this->logo)) {
            return;
        }

        $width = $this->logo->width() + 20;
        $height = $this->logo->height() + 10;

        if ($width > $this->width) {
            $this->width = $width;
        }

        $this->height += $height;
    }

    protected function fits(Image $image): bool
    {
        return $this->width <= $image->width() &&
            $this->height <= $image->height();
    }

    protected function back(Image $image): void
    {
        $image->drawRectangle(
            $this->x($image),
            $this->y($image),
            function (Rectangle $rectangle): void {
                $rectangle->size($this->width, $this->height);
                $rectangle->background('rgba(192, 192, 192, 0.5)');
            },
        );
    }

    protected function logo(Image $image): void
    {
        if (\is_null($this->logo)) {
            return;
        }

        $image->place(
            $this->logo,
            'top-left',
            $this->x($image) + 10,
            $this->y($image) + 10,
        );
    }

    protected function text(Image $image, string $text): void
    {
        $image->text(
            $text,
            $this->x($image) + 10,
            $this->y($image) + $this->height - 10,
            function (Font $font): void {
                $font->color('#ffffff');
                $font->filename($this->config->font);
                $font->size(16);
                $font->valign('bottom');
            },
        );
    }

    protected function encode(Image $image): EncodedImage
    {
        return $image->encode(new AutoEncoder());
    }

    protected function x(Image $image): int
    {
        return self::position(
            $this->width,
            $image->width(),
            0,
            $this->config->left,
        );
    }

    protected function y(Image $image): int
    {
        return self::position(
            $this->height,
            $image->height(),
            (int) \floor($image->height() / 4),
            $this->config->top,
        );
    }

    protected static function position(
        int $for,
        int $max,
        int $offset,
        bool $start,
    ): int {
        if ($start) {
            return $for + $offset > $max ? 0 : $offset;
        }

        return $max - $for - $offset < 0 ? 0 : $max - $for - $offset;
    }
}
