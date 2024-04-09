<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final readonly class Join
{
    /** @var array<Clause> */
    public array $clause;

    /**
     * @param InspectedEntityInterface<T> $entity
     *
     * @template T
     */
    public function __construct(
        public InspectedEntityInterface $entity,
        public string $type,
        public string $property,
        public bool $lazy,
        Clause ...$clause,
    ) {
        $this->clause = $clause;
    }
}
