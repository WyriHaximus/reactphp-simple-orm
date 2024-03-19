<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

#[Table('comments')]
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
    entity: BlogPostStub::class,
    clause: [
        new Clause(
            localKey: 'blog_post_id',
            foreignKey: 'id',
        ),
    ],
    property: 'blog_post',
)]
final readonly class CommentStub implements EntityInterface
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

    public function id(): string
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
