<?php
/**
 * Watermark Interface
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Watermark;

use Intervention\Image\Image;

/**
 * An interface used for watermarking an image.
 */
interface WatermarkInterface
{
    /**
     * Construct parameters of the watermark.
     * @param string $font Full path to a TTF font file used for text.
     * @param string $logo Full path to an image file of a logo to be added.
     * @param integer $width The preferred width of the watermark in pixels.
     * @param integer $height The preferred height of the watemark in pixels.
     * @param string $position The position of the watermark, as a combination
     *  of 'top' or 'bottom' and 'left' or 'right' with any separators.
     */
    public function __construct($font, $logo, $width, $height, $position);

    /**
     * Adds the watermark to an existing image.
     * @param Image $image Intervention Image object to add the watermark to.
     * @param string $text Text that should be added to the watermark.
     */
    public function addTo(Image &$image, $text = '');
}
