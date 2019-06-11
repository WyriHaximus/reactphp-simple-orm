<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class BlogPostsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'author_id' => 1,
                'publisher_id' => 1,
                'title' => 'Cats!',
                'contents' => 'qliwuhe uofq2hep fuoq2pho fp2uhu pu2p 2qpoh weh uwqhfu wqif',
            ],
            [
                'id' => 2,
                'author_id' => 2,
                'publisher_id' => 2,
                'title' => 'Moar Cats!',
                'contents' => 'qlqweofu b02qw yu9   dqiwuhe uofq2hep fuoq2pho fp2uhu pu2p 2qpoh weh uwqhfu wqif',
            ],
        ];

        $table = $this->table('blog_posts');
        $table->insert($data)->save();
    }
}
