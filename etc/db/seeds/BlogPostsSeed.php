<?php declare(strict_types=1);
use Phinx\Seed\AbstractSeed;

class BlogPostsSeed extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => '1',
                'author_id' => '1',
                'title' => 'Cats!',
                'contents' => 'qliwuhe uofq2hep fuoq2pho fp2uhu pu2p 2qpoh weh uwqhfu wqif',
            ],
        ];

        $table = $this->table('blog_posts');
        $table->insert($data)->save();
    }
}
