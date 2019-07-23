<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

/**
 * @Table("comments")
 * @InnerJoin(
        entity=UserStub::class,
        local_key="author_id",
        foreign_key="id",
        property="author"
 * )
 */
class CommentStub implements EntityInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $author_id;

    /** @var UserStub */
    protected $author;

    /** @var string */
    protected $blog_post_id;

    /** @var string */
    protected $contents;

    public function getId(): string
    {
        return $this->id;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getAuthor(): UserStub
    {
        return $this->author;
    }
}
