<?php
/**
 * Abstract Watermark Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Watermark;

/**
 * An abstract class with methods to be extended by all watermarks.
 */
abstract class AbstractWatermark
{
    /**
     * Typically, the full path to a TTF font file used for added text.
     * The default of 5 chooses the largest GD library internal font.
     * @var string|int
     */
    protected $font = 5;

    /**
     * The full path to an image file of a logo to be added.
     * @var string
     */
    protected $logo = '';

    /**
     * The preferred width of the watermark in pixels. The default value of 270
     * is an attempt to allow for 25 fixed width characters 10 pixels wide,
     * with 10 pixels of padding on each side.
     * @var int
     */
    protected $width = 270;

    /**
     * The preferred height of the watermark in pixels. The default value of 35
     * is an attempt to allow for one line of 15 pixel tall characters, with 10
     * pixels of padding on each side.
     * @var int
     */
    protected $height = 35;

    /**
     * The position of the watermark, as a combination of 'top' or 'bottom' and
     * 'left' or 'right' with any separators. The default places the watermark
     * in the bottom right of the image.
     * @var string
     */
    protected $position = 'bottom-right';

    /**
     * Construct parameters of the watermark.
     * @param string $font Full path to a TTF font file used for added text.
     * @param string $logo Full path to an image file of a logo to be added.
     * @param integer $width The preferred width of the watermark in pixels.
     * @param integer $height The preferred height of the watemark in pixels.
     * @param string $position The position of the watermark, as a combination
     *  of 'top' or 'bottom' and 'left' or 'right' with any separators.
     */
    public function __construct($font, $logo, $width, $height, $position)
    {
        $this->setFont($font);
        $this->setLogo($logo);
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setPosition($position);
    }

    /**
     * Sets the font to be used for added text.
     * @param string|int $font Preferrably, the full path to a TTF font file.
     *  May be an integer from 1 to 5 to specify a GD library internal font.
     */
    public function setFont($font)
    {
        // Set the font if it is a string name of an existing file.
        if (is_string($font) && $font !== '' && file_exists($font)) {
            $this->font = $font;
        }

        // Set the font if it is an integer from 1 to 5.
        if (is_numeric($font) && in_array($font, range(1, 5))) {
            $this->font = (int) $font;
        }
    }

    /**
     * Sets the image file of a logo to be added.
     * @param string $logo Full path to an image file of a logo to be added.
     */
    public function setLogo($logo)
    {
        // Set the logo if it is a string name of an existing file.
        if (is_string($logo) && $logo !== '' && file_exists($logo)) {
            $this->logo = $logo;
        }
    }

    /**
     * Sets the preferred width of the watermark in pixels.
     * @param integer $width The preferred width of the watermark in pixels.
     */
    public function setWidth($width)
    {
        // Set the width as an integer if it is a number greater than 0.
        if (is_numeric($width) && $width > 0) {
            $this->width = (int) $width;
        }
    }

    /**
     * Sets the preferred height of the watermark in pixels.
     * @param integer $height The preferred height of the watermark in pixels.
     */
    public function setHeight($height)
    {
        // Set the height as an integer if it is a number greater than 0.
        if (is_numeric($height) && height > 0) {
            $this->height = (int) $height;
        }
    }

    /**
     * Sets the position of the watermark.
     * @param string $position The position of the watermark as a combination
     *  of 'top' or 'bottom' and 'left' or 'right' with any separators.
     */
    public function setPosition($position)
    {
        // Set the position as long as it is a non-empty string.
        if (is_string($position) && $position !== '') {
            $this->position = $position;
        }
    }

    /**
     * Gets a specified range of numbers offset from an ending point.
     * @param int $total The total area to be positioned within.
     * @param int $range The range as a portion of the total area.
     * @param int $offset The offset for the range from the end of the area.
     * @return int[] The start and end of the range within the total.
     */
    protected static function getEndPosition($total, $range, $offset)
    {
        // Calculate the offset from the end of the total area.
        $end = $total - $offset;

        // If the range exceeds the total area, ignore the offset.
        if ($end - $range < 0) {
            $end = $total;
        }

        // Return the start and end of the range from the calculated offset.
        return [$end - $range, $end];
    }

    /**
     * Gets a specified range of numbers offset from a starting point.
     * @param int $total The total area to be positioned within.
     * @param int $range The range as a portion of the total area.
     * @param int $offset The offset for the range from the start of the area.
     * @return int[] The start and end of the range within the total.
     */
    protected static function getStartPosition($total, $range, $offset)
    {
        // Calculate the offset from the beginning of the total area.
        $start = 0 + $offset;

        // If the range exceeds the total area, ignore the offset.
        if ($start + $range > $total) {
            $start = 0;
        }

        // Return the start and end of the range from the calculated offset.
        return [$start, $start + $range];
    }
}
