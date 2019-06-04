<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up(): void
    {
        $this->table('users')
            ->addColumn('name', 'string', [
                'limit' => 256,
            ])
            ->create();

        $this->table('blog_posts')
            ->addColumn('author_id', 'integer', [
                'limit' => 20,
            ])
            ->addColumn('title', 'string', [
                'limit' => 256,
            ])
            ->addColumn('contents', 'string', [
                'limit' => 256,
            ])
            ->create();

        $this->table('comments')
            ->addColumn('author_id', 'integer', [
                'limit' => 20,
            ])
            ->addColumn('blog_post_id', 'integer', [
                'limit' => 20,
            ])
            ->addColumn('contents', 'string', [
                'limit' => 256,
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('users')->drop()->save();
        $this->table('comments')->drop()->save();
    }
}
