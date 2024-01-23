<?php

declare(strict_types=1);

namespace Migration;

use Phoenix\Database\Element\Index;
use Phoenix\Exception\InvalidArgumentValueException;
use Phoenix\Migration\AbstractMigration;

final class CreateLinkTable extends AbstractMigration
{
    /** @throws InvalidArgumentValueException */
    protected function up(): void
    {
        $this->table('link')
            ->addColumn('link', 'string', ['length' => 255])
            ->addIndex('link', Index::TYPE_UNIQUE)
            ->create();
    }

    /** @throws InvalidArgumentValueException */
    protected function down(): void
    {
        $this->table('link')->drop();
    }
}
