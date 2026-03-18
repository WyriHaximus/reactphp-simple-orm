<?php

declare(strict_types=1);

namespace WyriHaximus\DevApp\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('comments')]
final readonly class CommentStub implements EntityInterface
{
    use WithFieldsTrait;

    /** @phpstan-ignore shipmonk.deadMethod */
    public function __construct(
        public string $id,
        #[InnerJoin(
            clause: [
                new Clause(
                    localKey: 'author_id',
                    foreignKey: 'id',
                ),
            ],
        )]
        public UserStub $author,
        #[InnerJoin(
            clause: [
                new Clause(
                    localKey: 'blog_post_id',
                    foreignKey: 'id',
                ),
            ],
        )]
        public BlogPostStub $blogPost,
        public string $contents,
    ) {
    }
}
