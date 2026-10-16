<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Fixtures;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\LeftJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

#[Table('runtime_inspect_blog_posts_with_comments')]
final readonly class RuntimeInspectBlogPostWithCommentsStub implements EntityInterface
{
    /** @param iterable<RuntimeInspectCommentStub> $comments */
    public function __construct(
        public string $id,
        #[LeftJoin(
            clause: [
                new Clause(
                    localKey: 'id',
                    foreignKey: 'blog_post_id',
                    localCast: 'BIGINT',
                ),
            ],
        )]
        public iterable $comments,
    ) {
    }
}
