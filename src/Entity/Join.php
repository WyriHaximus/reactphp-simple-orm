<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final class Join
{
    private InspectedEntityInterface $entity;

    private string $type;

    private string $property;

    private bool $lazy;

    /** @var Clause[] */
    private array $clause;

    /**
     * @param array<int, Clause> $clause
     */
    public function __construct(InspectedEntityInterface $entity, string $type, string $property, bool $lazy, Clause ...$clause)
    {
        $this->entity   = $entity;
        $this->type     = $type;
        $this->property = $property;
        $this->lazy     = $lazy;
        $this->clause   = $clause;
    }

    public function getEntity(): InspectedEntityInterface
    {
        return $this->entity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getLazy(): bool
    {
        return $this->lazy;
    }

    /**
     * @return Clause[]
     */
    public function getClause(): array
    {
        return $this->clause;
    }
}
