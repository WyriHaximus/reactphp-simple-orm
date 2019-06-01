<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;

final class InspectedEntity
{
    /** @var string */
    private $class;

    /** @var string */
    private $table;

    /** @var Field[] */
    private $fields = [];

    public function __construct(string $class, string $table, array $fields)
    {
        $this->class = $class;
        $this->table = $table;
        $this->fields = $fields;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
