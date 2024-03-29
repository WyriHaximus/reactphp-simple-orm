<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use React\Promise\PromiseInterface;
use Rx\Observable;
use Safe\DateTimeImmutable;
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
            localCast: 'BIGINT',
            foreignKey: 'blog_post_id',
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

    //phpcs:disable
    protected string $id;

    protected ?string $previous_blog_post_id = null;

    /**
     * @var PromiseInterface<BlogPostStub>
     */
    protected PromiseInterface $previous_blog_post;

    protected ?string $next_blog_post_id = null;

    /**
     * @var PromiseInterface<BlogPostStub>
     */
    protected PromiseInterface $next_blog_post;

    protected string $author_id;

    protected string $publisher_id;

    protected string $title;

    protected string $contents;

    protected UserStub $author;

    protected UserStub $publisher;

    protected Observable $comments;

    protected int $views;

    protected string $created;

    protected string $modified;
    //phpcs:enable

    public function id(): string
    {
        return $this->id;
    }

    /** @return PromiseInterface<BlogPostStub> */
    public function getPreviousBlogPost(): PromiseInterface
    {
        //phpcs:disable
        return $this->previous_blog_post;
        //phpcs:enable
    }

    /** @return PromiseInterface<BlogPostStub> */
    public function getNextBlogPost(): PromiseInterface
    {
        //phpcs:disable
        return $this->next_blog_post;
        //phpcs:enable
    }

    public function getAuthor(): UserStub
    {
        return $this->author;
    }

    public function getPublisher(): UserStub
    {
        return $this->publisher;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getComments(): Observable
    {
        return $this->comments;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function withViews(int $views): self
    {
        $clone        = clone $this;
        $clone->views = $views;

        return $clone;
    }

    public function getCreated(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->created);
    }

    public function getModified(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->modified);
    }
}
