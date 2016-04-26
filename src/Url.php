<?php
/**
 * URL Class
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

namespace App;

use Aura\Sql\ExtendedPdoInterface;
use Aura\SqlQuery\QueryFactory;

/**
 * A class which allows for the normalization of a URL.
 */
class Url implements UrlInterface
{
    /**
     * Extended PDO connection to a database.
     * @var ExtendedPdoInterface
     */
    private $pdo;

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
     * Construct the implementation with objects and configuration.
     * @param ExtendedPdoInterface $pdo Extended PDO connection to a database.
     * @param QueryFactory $query SQL query factory.
     * @param string $prefix The prefix for tables within the database.
     */
    public function __construct(
        ExtendedPdoInterface $pdo,
        QueryFactory $query,
        $prefix
    ) {
        $this->pdo = $pdo;
        $this->query = $query;
        $this->prefix = $prefix;
    }

    /**
     * Normalize a URL.
     * @param string $url The URL to be normalized.
     * @return string The normalized URL.
     */
    public function normalize($url)
    {
        $normalizer = new \URL\Normalizer($url, true, true);
        return $normalizer->normalize();
    }

    /**
     * Saves a URL to the database if it has not already been saved.
     * @param string $url The url that should be saved.
     * @return integer The ID of the row for the url in the database.
     */
    public function save($url)
    {
        // Normalize the link.
        $link = $this->normalize($url);

        // Check if the link already exists in the database.
        $select = $this->query->newSelect();

        $select
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
