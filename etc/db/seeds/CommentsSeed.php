<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class CommentsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => '1',
                'author_id' => '1',
                'blog_post_id' => '1',
                'contents' => 'abc',
            ],
            [
                'id' => '2',
                'author_id' => '3',
                'blog_post_id' => '1',
                'contents' => 'def',
            ],
            [
                'id' => '3',
                'author_id' => '2',
                'blog_post_id' => '1',
                'contents' => 'ghi',
            ],
        ];

        $table = $this->table('comments');
        $table->insert($data)->save();
    }
}
