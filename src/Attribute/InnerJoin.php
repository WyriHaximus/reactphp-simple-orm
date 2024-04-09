<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class InnerJoin implements JoinInterface
{
    public string $type;

    /** @param array<Clause> $clause */
    public function __construct( /** @phpstan-ignore-line */
        public string $entity,
        public array $clause,
        public string $property,
        public bool $lazy = self::IS_NOT_LAZY,
    ) {
        $this->type = 'inner';
    }
}
