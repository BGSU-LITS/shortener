<?php
/**
 * Redirect Action Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App\Action;

use Aura\Sql\ExtendedPdoInterface;
use Hashids\HashGenerator;
use Aura\SqlQuery\QueryFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * A class to be invoked for the index action.
 */
class RedirectAction
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
     * SQL query factory.
     * @var QueryFactory
     */
    private $query;

    /**
     * The prefix for tables within the database.
     * @var string
     */
    private $prefix;

    /**
     * Construct the action with objects and configuration.
     * @param ExtendedPdoInterface $pdo Extended PDO connection to a database.
     * @param HashGenerator $hash Hash generator.
     * @param QueryFactory $query SQL query factory.
     * @param string $prefix he prefix for tables within the database.
     */
    public function __construct(
        ExtendedPdoInterface $pdo,
        HashGenerator $hash,
        QueryFactory $query,
        $prefix = ''
    ) {
        $this->pdo = $pdo;
        $this->hash = $hash;
        $this->query = $query;
        $this->prefix = $prefix;
    }

    /**
     * Method called when class is invoked as an action.
     * @param Request $req The request for the action.
     * @param Response $res The response from the action.
     * @param array $args The arguments for the action.
     * @return Response The response from the action.
     * @throws Exception The hash ID could not be decoded, or the link for the
     *  ID couldn't be found in the table.
     */
    public function __invoke(Request $req, Response $res, array $args)
    {
        // Unused.
        $req;

        // Convert the hash into an ID.
        $decoded = $this->hash->decode($args['hash']);

        // If the hash could be decoded into an ID:
        if (!empty($decoded[0])) {
            // Select from the links table the row identified by the ID.
            $select = $this->query->newSelect()
                ->cols(['link'])
                ->from($this->prefix. 'links')
                ->where('id = ?', $decoded[0]);

            // Fetch the link from that row.
            $link = $this->pdo->fetchValue(
                $select->getStatement(),
                $select->getBindValues()
            );

            // Redirect to that link if possible.
            if (!empty($link)) {
                return $res->withStatus(302)->withHeader('Location', $link);
            }

            // The row with the decoded ID couldn't be found in the table.
            throw new \Exception(
                sprintf('Link #%s could not be found.', $decoded[0])
            );
        }

        // The hash could not be decoded.
        throw new \Exception('The hash ID could not be decoded.');
    }
}
