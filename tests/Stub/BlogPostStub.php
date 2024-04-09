<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use EventSauce\ObjectHydrator\MapFrom;
use React\Promise\PromiseInterface;
use Rx\Observable;
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
    lazy: JoinInterface::IS_LAZY,
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
    lazy: JoinInterface::IS_LAZY,
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
     * @param PromiseInterface<BlogPostStub> $previousBlogPost
     * @param PromiseInterface<BlogPostStub> $nextBlogPost
     */
    public function __construct( /** @phpstan-ignore-line */
        public string $id,
        #[MapFrom('previous_blog_post_id')]
        public string|null $previousBlogPostId,
        public PromiseInterface $previousBlogPost,
        #[MapFrom('next_blog_post_id')]
        public string|null $nextBlogPostId,
        public PromiseInterface $nextBlogPost,
        #[MapFrom('author_id')]
        public string $authorId,
        #[MapFrom('publisher_id')]
        public string $publisherId,
        public string $title,
        public string $contents,
        public UserStub $author,
        public UserStub $publisher,
        public Observable $comments,
        public int $views,
        public string $created,
        public string $modified,
    ) {
    }
}
