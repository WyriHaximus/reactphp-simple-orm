<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up(): void
    {
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('name', 'string')
            ->create();

        $this->table('blog_posts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('author_id', 'uuid')
            ->addColumn('publisher_id', 'uuid')
            ->addColumn('title', 'string')
            ->addColumn('contents', 'string')
            ->addColumn('views', 'integer')
            ->create();

        $this->table('comments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid')
            ->addColumn('author_id', 'uuid')
            ->addColumn('blog_post_id', 'uuid')
            ->addColumn('contents', 'string')
            ->create();
    }

    public function down(): void
    {
        $this->table('users')->drop()->save();
        $this->table('comments')->drop()->save();
    }
}
