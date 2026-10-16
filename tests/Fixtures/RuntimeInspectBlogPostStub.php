<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Fixtures;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

#[Table('runtime_inspect_blog_posts')]
final readonly class RuntimeInspectBlogPostStub implements EntityInterface
{
    public function __construct(
        public string $id,
        #[InnerJoin(
            clause: [
                new Clause(
                    localKey: 'author_id',
                    foreignKey: 'id',
                ),
            ],
        )]
        public RuntimeInspectUserStub $author,
    ) {
    }
}
