<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class LazyInspectedEntity implements InspectedEntityInterface
{
    private string $class;

    private ?string $table = null;

    /** @var Field[] */
    private array $fields = [];

    /** @var Join[] */
    private array $joins = [];

    private ?EntityInspector $entityInspector = null;

    public function __construct(EntityInspector $entityInspector, string $class)
    {
        $this->class           = $class;
        $this->entityInspector = $entityInspector;
    }

    public function class(): string
    {
        return $this->class;
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    public function table(): string
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress NullableReturnStatement
         */
        return $this->table;
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->fields;
    }

    /**
     * @return Join[]
     */
    public function joins(): array
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->joins;
    }

    private function loadEntity(): void
    {
        if ($this->entityInspector === null) {
            return;
        }

        $inspectedEntity       = $this->entityInspector->entity($this->class);
        $this->entityInspector = null;

        $this->table  = $inspectedEntity->table();
        $this->fields = $inspectedEntity->fields();
        $this->joins  = $inspectedEntity->joins();
    }
}
