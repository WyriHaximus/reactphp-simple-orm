<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class InspectedEntity
{
    /** @var string */
    private $class;

    /** @var string */
    private $table;

    /** @var Field[] */
    private $fields = [];

    /** @var Join[] */
    private $joins = [];

    public function __construct(string $class, string $table, array $fields, array $joins)
    {
        $this->class = $class;
        $this->table = $table;
        $this->fields = $fields;
        $this->joins = $joins;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return Join[]
     */
    public function getJoins(): array
    {
        return $this->joins;
    }
}