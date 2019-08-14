<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class BlogPostsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => '53ab5832-9a90-4e6e-988b-06b8b5fed763',
                'previous_blog_post_id' => null,
                'next_blog_post_id' => '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
                'author_id' => 'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
                'publisher_id' => 'fb175cbc-04cc-41c7-8e35-6b817ac016ca',
                'title' => 'Cats!',
                'contents' => 'qliwuhe uofq2hep fuoq2pho fp2uhu pu2p 2qpoh weh uwqhfu wqif',
                'views' => 133,
                'created' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'modified' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
            [
                'id' => '090fa83b-5c5a-4042-9f05-58d9ab649a1a',
                'previous_blog_post_id' => '53ab5832-9a90-4e6e-988b-06b8b5fed763',
                'next_blog_post_id' => null,
                'author_id' => '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                'publisher_id' => '15f25357-4b3d-4d4d-b6a5-2ceb93864b77',
                'title' => 'Moar Cats!',
                'contents' => 'qlqweofu b02qw yu9   dqiwuhe uofq2hep fuoq2pho fp2uhu pu2p 2qpoh weh uwqhfu wqif',
                'views' => 166,
                'created' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'modified' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ],
        ];

        $table = $this->table('blog_posts');
        $table->insert($data)->save();
    }
}
