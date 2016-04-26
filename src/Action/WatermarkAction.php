<?php
/**
 * Watermark Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Action;

use Hashids\HashGenerator;
use Intervention\Image\ImageManager;
use App\UrlInterface;
use App\Watermark\WatermarkInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * A class to be invoked for the watermark action.
 */
class WatermarkAction
{
    /**
     * Hash generator.
     * @var HashGenerator
     */
    private $hash;

    /**
     * Image factory.
     * @var ImageManager
     */
    private $image;

    /**
     * URL normalizer.
     * @var UrlInterface
     */
    private $url;

    /**
     * Image watermarker.
     * @var WatermarkInterface
     */
    private $watermark;

    /**
     * Base file path to the application.
     * @var string
     */
    private $basepath;

    /**
     * Maximum number of pixels an image may contain to be watermarked.
     * @var integer
     */
    private $limit;

    /**
     * Construct the action with objects and configuration.
     * @param HashGenerator $hash Hash generator.
     * @param ImageManager $image Image factory.
     * @param UrlInterface $url URL normalizer.
     * @param WatermarkInterface $watermark Image watermarker.
     * @param string $basepath Base file path to the application.
     * @param int $limit Maximum number of pixels an image may contain.
     */
    public function __construct(
        HashGenerator $hash,
        ImageManager $image,
        UrlInterface $url,
        WatermarkInterface $watermark,
        $basepath,
        $limit
    ) {
        $this->hash = $hash;
        $this->image = $image;
        $this->url = $url;
        $this->watermark = $watermark;
        $this->basepath = $basepath;
        $this->limit = $limit;
    }

    /**
     * Method called when class is invoked as an action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     */
    public function __invoke(Request $req, Response $res, array $args)
    {
        // Determine the full path to the image.
        $path = realpath($this->basepath) . $args['path'];

        // Obtain the information about the image.
        $info = getimagesize($path);

        // If a MIME type is available, send it with the response.
        if (!empty($info['mime'])) {
            $res = $res->withHeader('Content-Type', $info['mime']);
        }

        // If the image's dimensions are within the limit:
        if (!empty($info[0]) && !empty($info[1])
         && $info[0] * $info[1] <= $this->limit) {
            // Attempt to load the requested image.
            $image = $this->image->make($path);

            // Get the current hostname as text for the watermark.
            $text = $req->getUri()->getHost();

            // Retrieve the server paramaters for the request.
            $params = $req->getServerParams();

            // If a referring ling was specified by the browser:
            if (!empty($params['HTTP_REFERER'])) {
                // Save that referring link to the database.
                $linkId = $this->url->save($params['HTTP_REFERER']);

                // Append the hash for the saved link to the text.
                $text .= '/' . $this->hash->encode($linkId);
            }

            // Add a watermark with text to the image.
            $this->watermark->addTo($image, $text);

            // Return the image as the response.
            return $res->write($image->encode());
        }

        // Return the original file as the response.
        return $res->write(file_get_contents($path));
    }
}
