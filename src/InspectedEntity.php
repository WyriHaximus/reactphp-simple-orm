<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class InspectedEntity implements InspectedEntityInterface
{
    private string $class;

    private string $table;

    /** @var Field[] */
    private array $fields = [];

    /** @var Join[] */
    private array $joins = [];

    /**
     * @param Field[] $fields
     * @param Join[]  $joins
     */
    public function __construct(string $class, string $table, array $fields, array $joins)
    {
        $this->class  = $class;
        $this->table  = $table;
        $this->fields = $fields;
        $this->joins  = $joins;
    }

    public function class(): string
    {
        return $this->class;
    }

    public function table(): string
    {
        return $this->table;
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return Join[]
     */
    public function joins(): array
    {
        return $this->joins;
    }
}
