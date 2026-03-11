<?php

declare(strict_types=1);

namespace WyriHaximus\DevApp\React\SimpleORM;

use DateTimeImmutable;
use EventSauce\ObjectHydrator\MapFrom;
use EventSauce\ObjectHydrator\PropertyCasters\CastToType;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\LeftJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('blog_posts')]
#[LeftJoin(
    entity: CommentStub::class,
    clause: [
        new Clause(
            localKey: 'id',
            foreignKey: 'blog_post_id',
            localCast: 'BIGINT',
        ),
    ],
    property: 'comments',
    lazy: JoinInterface::IS_LAZY,
)]
#[InnerJoin(
    entity: UserStub::class,
    clause: [
        new Clause(
            localKey: 'author_id',
            foreignKey: 'id',
        ),
    ],
    property: 'author',
)]
#[InnerJoin(
    entity: UserStub::class,
    clause: [
        new Clause(
            localKey: 'publisher_id',
            foreignKey: 'id',
        ),
    ],
    property: 'publisher',
)]
#[InnerJoin(
    entity: BlogPostStub::class,
    clause: [
        new Clause(
            localKey: 'previous_blog_post_id',
            foreignKey: 'id',
        ),
    ],
    property: 'previous_blog_post',
    lazy: JoinInterface::IS_LAZY,
)]
#[InnerJoin(
    entity: BlogPostStub::class,
    clause: [
        new Clause(
            localKey: 'next_blog_post_id',
            foreignKey: 'id',
        ),
    ],
    property: 'next_blog_post',
    lazy: JoinInterface::IS_LAZY,
)]
final readonly class BlogPostStub implements EntityInterface
{
    use WithFieldsTrait;

    /**
     * @param iterable<CommentStub> $comments
     *
     * @phpstan-ignore shipmonk.deadMethod,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration
     */
    public function __construct(
        public string $id,
        #[MapFrom('previous_blog_post')]
        public BlogPostStub|null $previousBlogPost,
        #[MapFrom('next_blog_post')]
        public BlogPostStub|null $nextBlogPost,
        public string $title,
        public string $contents,
        #[MapFrom('author')]
        public UserStub $author,
        #[MapFrom('publisher')]
        public UserStub $publisher,
        public iterable $comments,
        #[CastToType('integer')]
        public int $views,
        public DateTimeImmutable $created,
        public DateTimeImmutable $modified,
    ) {
    }
}
