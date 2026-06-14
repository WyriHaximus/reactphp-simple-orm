<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final readonly class Join
{
    /** @var array<Clause> */
    public array $clause;

    /**
     * @param InspectedEntityInterface<T> $entity
     *
     * @template T of EntityInterface
     */
    public function __construct(
        public InspectedEntityInterface $entity,
        public JointType $type,
        public string $property,
        public string $mapTo,
        public bool $lazy,
        Clause ...$clause,
    ) {
        $this->clause = $clause;
    }
}
