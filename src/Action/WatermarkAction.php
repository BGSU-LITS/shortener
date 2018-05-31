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
        // Check if a local image was specified as a parameter.
        if (!empty($args['path'])) {
            // Set the full path to the image.
            $args['path'] = realpath($this->basepath) . $args['path'];

            // Retrieve the server paramaters for the request.
            $params = $req->getServerParams();

            // If a referrer was specified, link back to it.
            if (!empty($params['HTTP_REFERER'])) {
                $args['link'] = $params['HTTP_REFERER'];
            }
        }

        // Get any files that were uploaded.
        $files = $req->getUploadedFiles();

        // If an image was uploaded successfully, set the path to the file.
        if (!empty($files['image'])
         && $files['image']->getError() === UPLOAD_ERR_OK) {
            $args['path'] = $files['image']->getStream()->getMetadata('uri');

            // Retrieve the posted data.
            $post = $req->getParsedBody();

            if (!empty($post['link'])) {
                $args['link'] = $post['link'];
            }
        }

        // The path to a valid file must be specified.
        if (empty($args['path']) || !file_exists($args['path'])) {
            return $res->withStatus(501);
        }

        // Return a response based on the updated arguments.
        return $this->respond($req, $res, $args);
    }

    /**
     * Responds to the invocation of the action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     */
    private function respond(Request $req, Response $res, array $args)
    {
        // Obtain the information about the image.
        $info = getimagesize($args['path']);

        // The file must be an image.
        if (empty($info)) {
            return $res->withStatus(501);
        }

        // If a MIME type is available, send it with the response.
        if (!empty($info['mime'])) {
            $res = $res->withHeader('Content-Type', $info['mime']);
        }

        // If the image's dimensions are within the limit:
        if (!empty($info[0]) && !empty($info[1])
         && $info[0] * $info[1] <= $this->limit) {
            // Attempt to load the requested image.
            $image = $this->image->make($args['path']);

            // Get the current hostname as text for the watermark.
            $text = $req->getUri()->getHost();

            // If a referring ling was specified by the browser:
            if (!empty($args['link'])
             && filter_var($args['link'], FILTER_VALIDATE_URL)) {
                // Save that referring link to the database.
                $linkId = $this->url->save($args['link']);

                // Append the hash for the saved link to the text.
                $text .= '/' . $this->hash->encode($linkId);
            }

            // Add a watermark with text to the image.
            $this->watermark->addTo($image, $text);

            // Return the watermarked image as the response.
            return $res->write($image->encode());
        }

        // Return the original file as the response.
        return $res->write(file_get_contents($args['path']));
    }
}
