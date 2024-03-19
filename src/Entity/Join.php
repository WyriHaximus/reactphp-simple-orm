<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final class Join
{
    /** @var array<Clause> */
    private array $clause;

    public function __construct(private InspectedEntityInterface $entity, private string $type, private string $property, private bool $lazy, Clause ...$clause)
    {
        $this->clause = $clause;
    }

    public function entity(): InspectedEntityInterface
    {
        return $this->entity;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function property(): string
    {
        return $this->property;
    }

    public function lazy(): bool
    {
        return $this->lazy;
    }

    /** @return Clause[] */
    public function clause(): array
    {
        return $this->clause;
    }
}
