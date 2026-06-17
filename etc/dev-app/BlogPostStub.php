<?php

declare(strict_types=1);

namespace WyriHaximus\DevApp\React\SimpleORM;

use DateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\CastToType;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\LeftJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('blog_posts')]
final readonly class BlogPostStub implements EntityInterface
{
    use WithFieldsTrait;

    /**
     * @param iterable<CommentStub> $comments
     *
     * @phpstan-ignore ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration
     */
    public function __construct(
        public string $id,
        #[InnerJoin(
            clause: [
                new Clause(
                    localKey: 'previous_blog_post_id',
                    foreignKey: 'id',
                ),
            ],
            lazy: JoinInterface::IS_LAZY,
        )]
        public BlogPostStub|null $previousBlogPost,
        #[InnerJoin(
            clause: [
                new Clause(
                    localKey: 'next_blog_post_id',
                    foreignKey: 'id',
                ),
            ],
            lazy: JoinInterface::IS_LAZY,
        )]
        public BlogPostStub|null $nextBlogPost,
        public string $title,
        public string $contents,
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
                    localKey: 'publisher_id',
                    foreignKey: 'id',
                ),
            ],
        )]
        public UserStub $publisher,
        #[LeftJoin(
            clause: [
                new Clause(
                    localKey: 'id',
                    foreignKey: 'blog_post_id',
                    localCast: 'BIGINT',
                ),
            ],
            lazy: JoinInterface::IS_LAZY,
        )]
        public iterable $comments,
        #[CastToType('integer')]
        public int $views,
        public DateTimeImmutable $created,
        public DateTimeImmutable $modified,
    ) {
    }
}
