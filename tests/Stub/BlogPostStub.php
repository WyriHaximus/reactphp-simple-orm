<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use Rx\Observable;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

/**
 * @Table("blog_posts")
 * @LeftJoin(
        entity=CommentStub::class,
        local_key="id",
        local_cast="BIGINT",
        foreign_key="blog_post_id",
        property="comments"
 * )
 * @InnerJoin(
        entity=UserStub::class,
        local_key="author_id",
        foreign_key="id",
        property="author"
 * )
 * @InnerJoin(
        entity=UserStub::class,
        local_key="publisher_id",
        foreign_key="id",
        property="publisher"
 * )
 */
class BlogPostStub implements EntityInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $author_id;

    /** @var string */
    protected $publisher_id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $contents;

    /** @var UserStub */
    protected $author;

    /** @var UserStub */
    protected $publisher;

    /** @var Observable */
    protected $comments;

    /** @var int */
    protected $views;

    /** @var \DateTimeImmutable */
    protected $created;

    /** @var \DateTimeImmutable */
    protected $modified;

    public function getId(): string
    {
        return $this->id;
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
        return $this->title;
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
        $clone = clone $this;
        $clone->views = $views;

        return $clone;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->created);
    }

    public function getModified(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->modified);
    }
}
