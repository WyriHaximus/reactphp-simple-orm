<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

use Attribute;

/** @api */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class LeftJoin implements JoinInterface
{
    public string $type;

    /**
     * @param array<Clause> $clause
     *
     * @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue
     */
    public function __construct(
        public string $entity,
        public array $clause,
        public string $property,
        public bool $lazy = self::IS_NOT_LAZY,
    ) {
        $this->type = 'left';
    }
}
