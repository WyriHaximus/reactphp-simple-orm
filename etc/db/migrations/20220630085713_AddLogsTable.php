<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class AddLogsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('message', 'string')
            ->addColumn('created', 'datetime', ['timezone' => true])
            ->addColumn('modified', 'datetime', ['timezone' => true])
            ->create();
    }
}
