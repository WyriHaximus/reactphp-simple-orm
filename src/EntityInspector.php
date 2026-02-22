<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use ReflectionClass;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use RuntimeException;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

use function array_key_exists;
use function count;
use function current;

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

            $roaveProperty = (
                static fn (BetterReflection $br, string $class): \Roave\BetterReflection\Reflection\ReflectionClass => $br->reflector()->reflectClass($class)
            )(new BetterReflection(), $class->getName())->getProperty($property->getName());

            if (! $roaveProperty instanceof ReflectionProperty) {
                continue;
            }

            yield $property->getName() => new Field(
                $property->getName(),
                (static function (ReflectionProperty $property): string {
                    $type = $property->getType();
                    if ($type !== null) {
                        return (string) $type;
                    }

                    return 'mixed';
                })($roaveProperty),
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
