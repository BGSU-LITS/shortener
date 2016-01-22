<?php
/**
 * Outside Watermark Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Watermark;

use Intervention\Image\Image;

/**
 * A class for the outside type of watermark.
 *
 * A box the full width positioned outside the top or bottom of an image.
 */
class OutsideWatermark extends InsideWatermark
{
    /**
     * Adjust the size of the image and/or watermark to fit.
     * @param Image $image Intervention Image object to be adjusted.
     */
    protected function adjustSize(Image &$image)
    {
        // Adjust the sizes of the image and watermark to fit.
        parent::adjustSize($image);

        // If the watermark will fit on the image:
        if ($this->isAdjusted($image->width(), $image->height())) {
            // Resize the image, adding the height of the watermark.
            $image->resizeCanvas(
                0,
                $this->height,
                strpos($this->position, 'top') === false ? 'top' : 'bottom',
                true
            );
        }
    }

    /**
     * Get a callback function to draw the background.
     * @return callable Draws the background.
     */
    protected function getBack()
    {
        return function ($draw) {
            $draw->background([51, 51, 51]);
        };
    }
}
