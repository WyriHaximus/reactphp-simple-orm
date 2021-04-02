<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final class Join
{
    private InspectedEntityInterface $entity;

    private string $type;

    private string $property;

    private bool $lazy;

    /** @var array<Clause> */
    private array $clause;

    public function __construct(InspectedEntityInterface $entity, string $type, string $property, bool $lazy, Clause ...$clause)
    {
        $this->entity   = $entity;
        $this->type     = $type;
        $this->property = $property;
        $this->lazy     = $lazy;
        $this->clause   = $clause;
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

    /**
     * @return Clause[]
     */
    public function clause(): array
    {
        return $this->clause;
    }
}
