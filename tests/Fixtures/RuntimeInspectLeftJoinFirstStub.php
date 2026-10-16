<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Fixtures;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\LeftJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;

#[Table('runtime_inspect_left_join_first')]
final readonly class RuntimeInspectLeftJoinFirstStub implements EntityInterface
{
    /** @param iterable<RuntimeInspectCommentStub> $comments */
    public function __construct(
        #[LeftJoin(
            clause: [
                new Clause(
                    localKey: 'id',
                    foreignKey: 'blog_post_id',
                    localCast: 'BIGINT',
                ),
            ],
        )]
        public iterable $comments,
        public string $id,
    ) {
    }
}
