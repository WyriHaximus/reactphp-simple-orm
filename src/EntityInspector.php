<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use EventSauce\ObjectHydrator\MapFrom;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

use function array_key_exists;
use function class_exists;
use function count;
use function current;
use function is_array;
use function is_string;

final class EntityInspector
{
    /**
     * @var array<InspectedEntityInterface<T>>
     * @template T of EntityInterface
     * @phpstan-ignore generics.notSubtype,class.notFound
     */
    private array $entities = [];

    public function __construct(
        private readonly Configuration $configuration,
    ) {
    }

    /**
     * @param class-string<T> $entity
     *
     * @return InspectedEntityInterface<T>
     *
     * @template T of EntityInterface
     */
    public function entity(string $entity): InspectedEntityInterface
    {
        if (! array_key_exists($entity, $this->entities)) {
            $class           = new ReflectionClass($entity);
            $tableAttributes = $class->getAttributes(Table::class);

            if (count($tableAttributes) === 0) {
                throw new RuntimeException('Missing Table annotation on entity: ' . $entity);
            }

            $tableAttribute = current($tableAttributes)->newInstance();

            $joins = [...$this->joins($class)];
            /** @phpstan-ignore assign.propertyType */
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $this->configuration->tablePrefix . $tableAttribute->table,
                [...$this->fields($class, $joins)],
                $joins,
            );
        }

        /** @phpstan-ignore return.type */
        return $this->entities[$entity];
    }

    /**
     * @param ReflectionClass<EntityInterface> $class
     * @param Join[]                           $joins
     *
     * @return iterable<string, Field>
     */
    private function fields(ReflectionClass $class, array $joins): iterable
    {
        foreach ($class->getProperties() as $property) {
            if (array_key_exists($property->getName(), $joins)) {
                continue;
            }

            $typeName = 'mixed';
            $type     = $property->getType();
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                if ($typeName === EntityInterface::class || (class_exists($typeName) && new ReflectionClass($typeName)->implementsInterface(EntityInterface::class))) {
                    continue;
                }

                if ($typeName === 'iterable' || $typeName === 'array') {
                    continue;
                }
            } elseif ($type instanceof ReflectionUnionType) {
                $isEntity = false;
                foreach ($type->getTypes() as $innerType) {
                    if (! ($innerType instanceof ReflectionNamedType)) {
                        continue;
                    }

                    $typeName = $innerType->getName();
                    if ($typeName === EntityInterface::class || (class_exists($typeName) && new ReflectionClass($typeName)->implementsInterface(EntityInterface::class))) {
                        $isEntity = true;
                        break;
                    }

                    if ($typeName === 'iterable' || $typeName === 'array') {
                        $isEntity = true;
                        break;
                    }
                }

                if ($isEntity) {
                    continue;
                }
            }

            $column = $property->getName();
            foreach ($property->getAttributes(MapFrom::class) as $attribute) {
                $keys = $attribute->getArguments()[0];
                if (is_string($keys)) {
                    $column = $keys;
                } elseif (is_array($keys) && count($keys) > 0 && is_string($keys[0])) {
                    $column = $keys[0];
                }
            }

            yield $property->getName() => new Field(
                $property->getName(),
                $column,
                $typeName,
            );
        }
    }

    /**
     * @param ReflectionClass<EntityInterface> $class
     *
     * @return iterable<string, Join>
     */
    private function joins(ReflectionClass $class): iterable
    {
        foreach ($class->getAttributes() as $attribute) {
            $annotation = $attribute->newInstance();
            if ($annotation instanceof JoinInterface === false) {
                continue;
            }

            yield from $this->join($annotation);
        }
    }

    /** @return iterable<string, Join> */
    private function join(JoinInterface $join): iterable
    {
        /** @phpstan-ignore generator.keyType,property.notFound */
        yield $join->property => new Join(
        /** @phpstan-ignore argument.type,property.notFound */
            new LazyInspectedEntity($this, $join->entity),
            /** @phpstan-ignore argument.type,property.notFound */
            $join->type,
            /** @phpstan-ignore argument.type,property.notFound */
            $join->property,
            /** @phpstan-ignore argument.type,property.notFound */
            $join->lazy,
            /** @phpstan-ignore argument.type,argument.unpackNonIterable,property.notFound */
            ...$join->clause,
        );
    }
}
