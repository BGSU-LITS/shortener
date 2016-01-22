<?php
/**
 * Inside Watermark Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Watermark;

use Intervention\Image\Image;

/**
 * A class for the inside type of watermark.
 *
 * A box covering the full width of the top or bottom of an image.
 */
class InsideWatermark extends DefaultWatermark
{
    /**
     * If the default watermark layout should be used.
     * @var boolean
     */
    protected $default = false;

    /**
     * Add text to the watermark being applied to an existing image.
     * @param Image $image Intervention Image object to add the logo to.
     * @param array $dimensions Two arrays of two integers. The first array
     *  contains the starting and ending x-position of the watermark. The
     *  second contains the starting and ending y-position.
     * @param string $text The text to be added to the watermark.
     */
    protected function addText(Image & $image, array $dimensions, $text)
    {
        // If necessary, use the default watermark layout.
        if ($this->default) {
            return parent::addText($image, $dimensions, $text);
        }

        // If text was specified:
        if (!empty($text)) {
            // Add the text to the right of the watermark, positioned in the
            // vertical middle, adding 10 pixels of padding to the right.
            $image->text(
                $text,
                $dimensions[0][1] - 10,
                $dimensions[1][1] - ($this->height / 2),
                $this->getFont()
            );
        }
    }

    /**
     * Adjust the size of the image and/or watermark to fit.
     * @param Image $image Intervention Image object to be adjusted.
     */
    protected function adjustSize(Image & $image)
    {
        // If a logo was specified, and is an existing file:
        if (self::isFile($this->logo)) {
            // Determine the width and height of the logo, adding 10 pixels of
            // padding to the left, and 10 pixels to the top and bottom.
            $logoSize = getimagesize($this->logo);
            $logoWidth = $logoSize[0] + 10;
            $logoHeight = $logoSize[1] + 20;

            // If the width of the logo plus the width of the watermark
            // is greater than the width of the entire image:
            if ($logoWidth + $this->width >= $image->width()) {
                // Use the layout from the default watermark.
                $this->default = true;

                // Use the default watermark to adjust the sizes.
                return parent::adjustSize($image);
            }

            // Add the logo's width to the watermark's width.
            $this->width += $logoWidth;

            // If the height of the logo is larger than the current watermark
            // height, increase the watermark height to the logo's width.
            if ($logoHeight > $this->height) {
                $this->height = $logoHeight;
            }
        }
    }

    /**
     * Get a callback function to setup the font.
     * @return \Closure Setup the font.
     */
    protected function getFont()
    {
        // If necessary, use the default watermark layout.
        if ($this->default) {
            return parent::getFont();
        }

        // Preffered is right aligned and middle vertical aligned.
        return function ($font) {
            $font->align('right');
            $font->color([255, 255, 255]);
            $font->file($this->font);
            $font->size(16);
            $font->valign('center');
        };
    }

    /**
     * Gets the horizontal dimensions of the watermark.
     * @param int $width The width of an entire image.
     * @return integer[] The starting and ending x-position of the watermark.
     */
    protected function getDimensionsX($width)
    {
        // Use the entire width of the image.
        return [0, $width];
    }

    protected function getDimensionsY($height)
    {
        // If the watermark is not positioned at the top:
        if (strpos($this->position, 'top') === false) {
            // Return the dimensions offset from the end.
            return self::getEndPosition($height, $this->height, 0);
        }

        // Return the dimensions offset from the start.
        return self::getStartPosition($height, $this->height, 0);
    }
}
