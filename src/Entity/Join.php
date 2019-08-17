<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\Annotation\Clause;
use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final class Join
{
    /** @var InspectedEntityInterface */
    private $entity;

    /** @var string */
    private $type;

    /** @var string */
    private $property;

    /** @var Clause[] */
    private $clause;

    public function __construct(InspectedEntityInterface $entity, string $type, string $property, Clause ...$clause)
    {
        $this->entity = $entity;
        $this->type = $type;
        $this->property = $property;
        $this->clause = $clause;
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

    /**
     * @return Clause[]
     */
    public function getClause(): array
    {
        return $this->clause;
    }
}
