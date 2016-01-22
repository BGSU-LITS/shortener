<?php
/**
 * Default Watermark Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Watermark;

use Intervention\Image\Image;

/**
 * A class for the default type of watermark.
 *
 * A box positioned over the image on either the left or right edge 1/4th of
 * the image height away from the top or bottom.
 */
class DefaultWatermark extends AbstractWatermark implements WatermarkInterface
{
    /**
     * Adds the watermark to an existing image.
     * @param Image $image Intervention Image object to add the watermark to.
     * @param string $text Any text that should be added to the watermark.
     */
    public function addTo(Image &$image, $text = '')
    {
        // Adjust the sizes of the image and watermark to fit.
        $this->adjustSize($image);
        $width = $image->width();
        $height = $image->height();

        // If the watermark will fit on the image:
        if ($this->isAdjusted($width, $height)) {
            // Get the dimensions of the watermark.
            $dimensions = [
                $this->getDimensionsX($width),
                $this->getDimensionsY($height)
            ];

            // Add the watermark background, logo and text to the image.
            $this->addBack($image, $dimensions);
            $this->addLogo($image, $dimensions);
            $this->addText($image, $dimensions, $text);
        }
    }

    /**
     * Add the background of a watermark to an existing image.
     * @param Image $image Intervention Image object to add the background to.
     * @param array $dimensions Two arrays of two integers. The first array
     *  contains the starting and ending x-position of the watermark. The
     *  second contains the starting and ending y-position.
     */
    protected function addBack(Image &$image, array $dimensions)
    {
        // Create a rectangular background at the provided coordinates.
        $image->rectangle(
            $dimensions[0][0],
            $dimensions[1][0],
            $dimensions[0][1],
            $dimensions[1][1],
            $this->getBack()
        );
    }

    /**
     * Add the specified logo for the watermark to an existing image.
     * @param Image $image Intervention Image object to add the logo to.
     * @param array $dimensions Two arrays of two integers. The first array
     *  contains the starting and ending x-position of the watermark. The
     *  second contains the starting and ending y-position.
     */
    protected function addLogo(Image &$image, array $dimensions)
    {
        // If a logo was specified, and is an existing file:
        if (self::isFile($this->logo)) {
            // Insert the logo into the top left of the watermark,
            // adding 10 pixels of padding to the top and left.
            $image->insert(
                $this->logo,
                'top-left',
                $dimensions[0][0] + 10,
                $dimensions[1][0] + 10
            );
        }
    }

    /**
     * Add text to the watermark being applied to an existing image.
     * @param Image $image Intervention Image object to add the logo to.
     * @param array $dimensions Two arrays of two integers. The first array
     *  contains the starting and ending x-position of the watermark. The
     *  second contains the starting and ending y-position.
     * @param string $text The text to be added to the watermark.
     */
    protected function addText(Image &$image, array $dimensions, $text)
    {
        // If text was specified:
        if (!empty($text)) {
            // Add the text to the bottom left of the watermark,
            // adding 10 pixels of padding to the bottom and left.
            $image->text(
                $text,
                $dimensions[0][0] + 10,
                $dimensions[1][1] - 10,
                $this->getFont()
            );
        }
    }

    /**
     * Adjust the size of the image and/or watermark to fit.
     * @param Image $image Intervention Image object to be adjusted.
     */
    protected function adjustSize(Image &$image)
    {
        // Unused.
        $image;

        // If a logo was specified, and is an existing file:
        if (self::isFile($this->logo)) {
            // Determine the width and height of the logo, adding 10 pixels of
            // padding to the left and right, and 10 pixels to the bottom.
            $logoSize = getimagesize($this->logo);
            $logoWidth = $logoSize[0] + 20;
            $logoHeight = $logoSize[1] + 10;

            // If the width of the logo is larger than the current watermark
            // width, increase the watermark width to the logo's width.
            if ($logoWidth > $this->width) {
                $this->width = $logoWidth;
            }

            // Add the logo's height to the watermark's height.
            $this->height += $logoHeight;
        }
    }

    /**
     * Get a callback function to draw the background.
     * @return callable Draws the background.
     */
    protected function getBack()
    {
        return function ($draw) {
            $draw->background([192, 192, 192, 0.5]);
        };
    }

    /**
     * Get a callback function to setup the font.
     * @return callable Setup the font.
     */
    protected function getFont()
    {
        return function ($font) {
            $font->color([255, 255, 255]);
            $font->file($this->font);
            $font->size(16);
            $font->valign('bottom');
        };
    }

    /**
     * Gets the horizontal dimensions of the watermark.
     * @param int $width The width of an entire image.
     * @return int[] The starting and ending x-position of the watermark.
     */
    protected function getDimensionsX($width)
    {
        // If the watermark is not positioned on the left:
        if (strpos($this->position, 'left') === false) {
            // Return the dimensions offset from the end.
            return self::getEndPosition($width, $this->width, 0);
        }

        // Return the dimensions offset from the start.
        return self::getStartPosition($width, $this->width, 0);
    }

    /**
     * Gets the vertical dimensions of the watermark.
     * @param int $height The height of an entire image.
     * @return int[] The starting and ending y-position of the watermark.
     */
    protected function getDimensionsY($height)
    {
        // The offset will be 1/4th of the total image height.
        $offset = floor($height / 4);

        // If the watermark is not positioned at the top:
        if (strpos($this->position, 'top') === false) {
            // Return the dimensions offset from the end.
            return self::getEndPosition($height, $this->height, $offset);
        }

        // Return the dimensions offset from the start.
        return self::getStartPosition($height, $this->height, $offset);
    }

    /**
     * Checks if the image and watermark sizes have been adjusted to fit.
     * @param integer $width The pixel width of the image.
     * @param integer $height The pixel height of the image.
     * @return bool The watermark will fit on the image.
     */
    protected function isAdjusted($width, $height)
    {
        return $this->width <= $width && $this->height <= $height;
    }
}
