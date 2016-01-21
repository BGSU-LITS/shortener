<?php
/**
 * Create Links Table Phinx Migration
 * @author John Kloor <kloor@bgsu.edu>
 * @copyright 2016 Bowling Green State University Libraries
 * @license MIT
 * @package Shortener
 */

use Phinx\Migration\AbstractMigration;

/** A migration to create the links table. */
class CreateLinksTable extends AbstractMigration
{
    /** Changes the database for the migration. */
    public function change()
    {
        $this->table('shortener_links')
            ->addColumn('link', 'string')
            ->addIndex('link', ['unique' => true])
            ->create();
    }
}
