<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class LazyInspectedEntity implements InspectedEntityInterface
{
    private string|null $table = null;

    /** @var Field[] */
    private array $fields = [];

    /** @var Join[] */
    private array $joins = [];

    public function __construct(private EntityInspector|null $entityInspector, private readonly string $class)
    {
    }

    public function class(): string
    {
        return $this->class;
    }

    public function table(): string
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->table;
    }

    /** @return Field[] */
    public function fields(): array
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->fields;
    }

    /** @return Join[] */
    public function joins(): array
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->joins;
    }

    private function loadEntity(): void
    {
        if (! $this->entityInspector instanceof EntityInspector) {
            return;
        }

        $inspectedEntity       = $this->entityInspector->entity($this->class);
        $this->entityInspector = null;

        $this->table  = $inspectedEntity->table();
        $this->fields = $inspectedEntity->fields();
        $this->joins  = $inspectedEntity->joins();
    }
}
