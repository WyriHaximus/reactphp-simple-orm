<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use React\Promise\PromiseInterface;
use Rx\Observable;
use Safe\DateTimeImmutable;
use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

/**
 * @Table("blog_posts")
 * @LeftJoin(
        nonExistingProperty="fake",
        entity=CommentStub::class,
        clause={
            @Clause(
                local_key="id",
                local_cast="BIGINT",
                foreign_key="blog_post_id",
            )
        },
        property="comments",
        lazy=LeftJoin::IS_LAZY
 * )
 * @InnerJoin(
        entity=UserStub::class,
        clause={
            @Clause(
                local_key="author_id",
                foreign_key="id",
            )
        },
        property="author",
        lazy=InnerJoin::IS_NOT_LAZY
 * )
 * @InnerJoin(
        entity=UserStub::class,
        clause={
            @Clause(
                local_key="publisher_id",
                foreign_key="id",
            )
        },
        property="publisher",
        lazy=InnerJoin::IS_NOT_LAZY
 * )
 * @InnerJoin(
        entity=BlogPostStub::class,
        clause={
            @Clause(
                local_key="previous_blog_post_id",
                foreign_key="id",
           )
        },
        property="previous_blog_post",
        lazy=InnerJoin::IS_LAZY
 * )
 * @InnerJoin(
        entity=BlogPostStub::class,
        clause={
            @Clause(
                local_key="next_blog_post_id",
                foreign_key="id",
            )
        },
        property="next_blog_post",
       lazy=InnerJoin::IS_NOT_LAZY
 * )
 */
final class BlogPostStub implements EntityInterface
{
    use WithFieldsTrait;

    //phpcs:disable
    protected string $id;

    protected ?string $previous_blog_post_id = null;

    protected PromiseInterface $previous_blog_post;

    protected ?string $next_blog_post_id = null;

    protected PromiseInterface $next_blog_post;

    protected string $author_id;

    protected string $publisher_id;

    protected string $title;

    protected string $contents;

    protected UserStub $author;

    protected UserStub $publisher;

    protected Observable $comments;

    protected int $views;

    protected DateTimeImmutable $created;

    protected DateTimeImmutable $modified;
    //phpcs:enable

    public function id(): string
    {
        return $this->id;
    }

    public function getPreviousBlogPost(): PromiseInterface
    {
        //phpcs:disable
        return $this->previous_blog_post;
        //phpcs:enable
    }

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
        return $this->created;
    }

    public function getModified(): DateTimeImmutable
    {
        return $this->modified;
    }
}
