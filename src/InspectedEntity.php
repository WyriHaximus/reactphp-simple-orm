<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

/**
 * @template T of EntityInterface
 * @template-implements InspectedEntityInterface<T>
 */
final readonly class InspectedEntity implements InspectedEntityInterface
{
    /**
     * @param Field[]         $fields
     * @param Join[]          $joins
     * @param class-string<T> $class
     *
     * @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noConstructorParameterWithDefaultValue
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
