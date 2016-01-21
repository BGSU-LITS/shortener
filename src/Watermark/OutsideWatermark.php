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
     * @return boolean If the watermark can fit within the image.
     */
    protected function adjustSize(Image &$image)
    {
        // If the watermark will fit the image:
        if (parent::adjustSize($image)) {
            // Resize the image, adding the height of the watermark.
            $image->resizeCanvas(
                0,
                $this->height,
                strpos($this->position, 'top') === false ? 'top' : 'bottom',
                true
            );

            // Return that the watermark will fit.
            return true;
        }

        // Return that the watermark will not fit.
        return false;
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
