<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

/**
 * @Table("comments")
 * @InnerJoin(
        entity=UserStub::class,
        clause={
            @Clause(
                local_key="author_id",
                foreign_key="id",
            )
        },
        property="author"
 * )
 * @InnerJoin(
        entity=BlogPostStub::class,
        clause={
            @Clause(
                local_key="blog_post_id",
                foreign_key="id",
            )
        },
        property="blog_post"
 * )
 */
final class CommentStub implements EntityInterface
{
    use WithFieldsTrait;

    //phpcs:disable
    protected string $id;

    protected string $author_id;

    protected UserStub $author;

    protected string $blog_post_id;

    protected BlogPostStub $blog_post;

    protected string $contents;
    //phpcs:enable

    public function getId(): string
    {
        return $this->id;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getBlogPost(): BlogPostStub
    {
        //phpcs:disable
        return $this->blog_post;
        //phpcs:enable
    }

    public function getAuthor(): UserStub
    {
        return $this->author;
    }
}
