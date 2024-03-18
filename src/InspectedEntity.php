<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class InspectedEntity implements InspectedEntityInterface
{
    /**
     * @param Field[] $fields
     * @param Join[]  $joins
     */
    public function __construct(private string $class, private string $table, private array $fields = [], private array $joins = [])
    {
    }

    public function class(): string
    {
        return $this->class;
    }

    public function table(): string
    {
        return $this->table;
    }

    /** @return Field[] */
    public function fields(): array
    {
        return $this->fields;
    }

    /** @return Join[] */
    public function joins(): array
    {
        return $this->joins;
    }
}
