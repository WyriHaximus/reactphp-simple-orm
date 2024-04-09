<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use EventSauce\ObjectHydrator\MapFrom;
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

    public function __construct(
        public string $id,
        #[MapFrom('author_id')]
        public string $authorId,
        public UserStub $author,
        #[MapFrom('blog_post_id')]
        public string $blogPostId,
        public BlogPostStub $blogPost,
        public string $contents,
    ) {
    }
}
