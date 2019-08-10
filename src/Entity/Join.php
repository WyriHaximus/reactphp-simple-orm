<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

use WyriHaximus\React\SimpleORM\InspectedEntityInterface;

final class Join
{
    /** @var InspectedEntityInterface */
    private $entity;

    /** @var string */
    private $type;

    /** @var string */
    private $localKey;

    /** @var string|null */
    private $localCast;

    /** @var string */
    private $foreignKey;

    /** @var string|null */
    private $foreignCast;

    /** @var string */
    private $property;

    public function __construct(InspectedEntityInterface $entity, string $type, string $localKey, ?string $localCast, string $foreignKey, ?string $foreignCast, string $property)
    {
        $this->entity = $entity;
        $this->type = $type;
        $this->localKey = $localKey;
        $this->localCast = $localCast;
        $this->foreignKey = $foreignKey;
        $this->foreignCast = $foreignCast;
        $this->property = $property;
    }

    public function getEntity(): InspectedEntityInterface
    {
        return $this->entity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getLocalCast(): ?string
    {
        return $this->localCast;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getForeignCast(): ?string
    {
        return $this->foreignCast;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
