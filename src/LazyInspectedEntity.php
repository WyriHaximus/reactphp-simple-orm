<?php declare(strict_types=1);

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

    public function getClass(): string
    {
        return $this->class;
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function getTable(): string
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        /** @psalm-suppress NullableReturnStatement */
        return $this->table;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        if ($this->table === null) {
            $this->loadEntity();
        }

        return $this->fields;
    }

    /**
     * @return Join[]
     */
    public function getJoins(): array
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

        $inspectedEntity       = $this->entityInspector->getEntity($this->class);
        $this->entityInspector = null;

        $this->table  = $inspectedEntity->getTable();
        $this->fields = $inspectedEntity->getFields();
        $this->joins  = $inspectedEntity->getJoins();
    }
}
