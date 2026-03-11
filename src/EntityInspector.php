<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

//use EventSauce\ObjectHydrator\MapFrom;
use EventSauce\ObjectHydrator\MapFrom;
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
use function is_array;
use function is_string;
use function method_exists;

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
     * @param array<string, Join>              $joins
     *
     * @return iterable<string, Field>
     */
    private function fields(ReflectionClass $class, array $joins): iterable
    {
        foreach ($class->getProperties() as $property) {
            $propertyName = $property->getName();

            $roaveProperty = (static function (BetterReflection $br, string $class): \Roave\BetterReflection\Reflection\ReflectionClass {
                if (method_exists($br, 'classReflector')) {
                    return $br->classReflector()->reflect($class);
                }

                return $br->reflector()->reflectClass($class);
            })(new BetterReflection(), $class->getName())->getProperty($propertyName);

            if (! $roaveProperty instanceof ReflectionProperty) {
                continue;
            }

            $column = $propertyName;
            foreach ($property->getAttributes(MapFrom::class) as $attribute) {
                $keys = $attribute->getArguments()[0];
                if (is_string($keys)) {
                    $column = $keys;
                } elseif (is_array($keys) && count($keys) > 0) {
                    $column = $keys[0];
                }
            }

//            if (array_key_exists($property->getName(), $joins) && $joins[$property->getName()]->type === JointType::LEFT) {
            if (array_key_exists($propertyName, $joins) || array_key_exists((string) $column, $joins)) {
                foreach ($joins as $key => $join) {
                    if ($join->property !== $propertyName && $join->property !== (string) $column) {
                        continue;
                    }

                    foreach ($join->clause as $clause) {
                        yield $clause->localKey => new Field(
                            $clause->localKey,
                            $clause->localKey,
                            'mixed',
                        );
                    }
                }

                continue;
            }

//            if (array_key_exists($property->getName(), $joins) && $joins[$property->getName()]->type === JointType::INNER) {
//            if (array_key_exists($property->getName(), $joins)) {
//                foreach ($joins[$property->getName()]->clause as $clause) {
//                    if ($clause->localKey === $column) {
////                        $propertyName = $joins[$property->getName()]->property;
//                        $propertyName = $column;
//                        break;
//                    }
//                }
//            }

            yield $propertyName => new Field(
                $propertyName,
                $column,
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
