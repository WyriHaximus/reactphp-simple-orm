<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;

/**
 * @Table("blog_posts")
 * @InnerJoin(
        entity=UserStub::class,
        local_key="author_id",
        foreign_key="id",
        property="author"
 * )
 * @LeftJoin(
        entity=CommentStub::class,
        local_key="id",
        local_cast="BIGINT",
        foreign_key="blog_post_id",
        property="comments"
 * )
 */
class BlogPostStub
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $author_id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $contents;

    /** @var UserStub */
    protected $author;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthor(): UserStub
    {
        return $this->author;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContents(): string
    {
        return $this->title;
    }
}
