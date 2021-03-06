<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('name', 'string')
            ->addColumn('created', 'datetime', ['timezone' => true])
            ->addColumn('modified', 'datetime', ['timezone' => true])
            ->create();

        $this->table('blog_posts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('previous_blog_post_id', 'uuid', ['null' => true])
            ->addColumn('next_blog_post_id', 'uuid', ['null' => true])
            ->addColumn('author_id', 'uuid')
            ->addColumn('publisher_id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('contents', 'string')
            ->addColumn('views', 'integer')
            ->addColumn('created', 'datetime', ['timezone' => true])
            ->addColumn('modified', 'datetime', ['timezone' => true])
            ->create();

        $this->table('comments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('author_id', 'uuid')
            ->addColumn('blog_post_id', 'uuid')
            ->addColumn('contents', 'string')
            ->addColumn('created', 'datetime', ['timezone' => true])
            ->addColumn('modified', 'datetime', ['timezone' => true])
            ->create();
    }
}
