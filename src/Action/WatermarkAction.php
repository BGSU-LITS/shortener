<?php
/**
 * Watermark Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Action;

use Aura\Sql\ExtendedPdoInterface;
use Hashids\HashGenerator;
use Intervention\Image\ImageManager;
use Aura\SqlQuery\QueryFactory;
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
     * Extended PDO connection to a database.
     * @var ExtendedPdoInterface
     */
    private $pdo;

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
     * SQL query factory.
     * @var QueryFactory
     */
    private $query;

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
     * The prefix for tables within the database.
     * @var string
     */
    private $prefix;

    /**
     * Construct the action with objects and configuration.
     * @param ExtendedPdoInterface $pdo Extended PDO connection to a database.
     * @param HashGenerator $hash Hash generator.
     * @param ImageManager $image Image factory.
     * @param QueryFactory $query SQL query factory.
     * @param UrlInterface $url URL normalizer.
     * @param WatermarkInterface $watermark Image watermarker.
     * @param string $basepath Base file path to the application.
     * @param int $limit Maximum number of pixels an image may contain.
     * @param string $prefix The prefix for tables within the database.
     */
    public function __construct(
        ExtendedPdoInterface $pdo,
        HashGenerator $hash,
        ImageManager $image,
        QueryFactory $query,
        UrlInterface $url,
        WatermarkInterface $watermark,
        $basepath,
        $limit,
        $prefix
    ) {
        $this->pdo = $pdo;
        $this->hash = $hash;
        $this->image = $image;
        $this->query = $query;
        $this->url = $url;
        $this->watermark = $watermark;
        $this->basepath = $basepath;
        $this->limit = $limit;
        $this->prefix = $prefix;
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
        // Retrieve the server paramaters for the request.
        $params = $req->getServerParams();

        // Determine the full path to the image.
        $path = realpath($this->basepath) . $args['path'];

        // Obtain the information about the image.
        $info = getimagesize($path);

        // If a MIME type is available, send it with the response.
        if (!empty($info['mime'])) {
            $res = $res->withHeader('Content-Type', $info['mime']);
        }

        // If the user was redirected to the watermark URI,
        // and the image's dimensions are within the limit:
        if ($params['REDIRECT_URL'] === '/watermark'
         && !empty($info[0])
         && !empty($info[1])
         && $info[0] * $info[1] <= $this->limit) {
            // Attempt to load the requested image.
            $image = $this->image->make($path);

            // Get the current hostname as text for the watermark.
            $text = $req->getUri()->getHost();

            // If a referring ling was specified by the browser:
            if (!empty($params['HTTP_REFERER'])) {
                // Save that referring link to the database.
                $linkId = $this->saveLink($params['HTTP_REFERER']);

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

    /**
     * Saves a link to the database if it has not already been saved.
     * @param string $link The link that should be saved.
     * @return integer The ID of the row for the link in the database.
     */
    private function saveLink($link)
    {
        // Normalize the link.
        $link = $this->url->normalize($link);

        // Check if the link already exists in the database.
        $select = $this->query->newSelect()
            ->cols(['id'])
            ->from($this->prefix . 'links')
            ->where('link = ?', $link);

        // Retrieve the ID of the existing row if found.
        $linkId = $this->pdo->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );

        // If the link does not exist in the database:
        if (empty($linkId)) {
            // Insert the link into the database.
            $insert = $this->query->newInsert()
                ->into($this->prefix . 'links')
                ->cols(['link'])
                ->bindValue('link', $link);

            $this->pdo->perform(
                $insert->getStatement(),
                $insert->getBindValues()
            );

            // Retrieve the ID of the inserted row.
            $linkId = $this->pdo->lastInsertId(
                $insert->getLastInsertIdName('id')
            );
        }

        // Return the ID of the row containing the link in the database.
        return $linkId;
    }
}
