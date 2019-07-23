<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class CommentsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => '326b9f10-10d9-46a9-8487-c34e189abdf1',
                'author_id' => 'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
                'blog_post_id' => '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
                'contents' => 'abc',
            ],
            [
                'id' => '82aafe95-d37e-4525-90f0-08a9fe674591',
                'author_id' => '2fa0d077-d374-4409-b1ef-9687c6729158',
                'blog_post_id' => '53ab5832-9a90-4e6e-988b-06b8b5fed763',
                'contents' => 'def',
            ],
            [
                'id' => '2443dfa0-b964-4a2a-9d4f-7e3c8aac23a3',
                'author_id' => '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                'blog_post_id' => '53ab5832-9a90-4e6e-988b-06b8b5fed763',
                'contents' => 'ghi',
            ],
        ];

        $table = $this->table('comments');
        $table->insert($data)->save();
    }
}
