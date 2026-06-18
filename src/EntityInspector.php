<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use EventSauce\ObjectHydrator\MapFrom;
use ICanBoogie\StaticInflector;
use ReflectionClass;
use ReflectionNamedType;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use RuntimeException;
use WyriHaximus\React\SimpleORM\Attribute\JoinInterface;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;
use WyriHaximus\React\SimpleORM\Entity\JointType;
use WyriHaximus\React\SimpleORM\Generated\InspectedEntityMap;
use WyriHaximus\React\SimpleORM\Tools\NaivePropertyTypeResolver;

use function array_key_exists;
use function class_exists;
use function count;
use function current;
use function in_array;
use function is_array;
use function is_string;
use function is_subclass_of;
use function method_exists;
use function str_replace;

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
        if (! array_key_exists($entity, $this->entities) && array_key_exists($entity, InspectedEntityMap::MAP) && class_exists(InspectedEntityMap::MAP[$entity])) {
            $this->entities[$entity] = new (InspectedEntityMap::MAP[$entity]);
        }

        if (! array_key_exists($entity, $this->entities)) {
            $class           = new ReflectionClass($entity);
            $tableAttributes = $class->getAttributes(Table::class);

            if (count($tableAttributes) === 0) {
                throw new RuntimeException('Missing Table annotation on entity: ' . $entity);
            }

            $tableAttribute = current($tableAttributes)->newInstance();

            $fields = $joins = [];
            foreach ($this->fields($class) as $fieldOrJoin) {
                if ($fieldOrJoin instanceof Field) {
                    $fields[] = $fieldOrJoin;
                } elseif ($fieldOrJoin instanceof Join) {
                    $joins[] = $fieldOrJoin;
                }
            }

            /** @phpstan-ignore assign.propertyType */
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $this->configuration->tablePrefix . $tableAttribute->table,
                $fields,
                $joins,
            );
        }

        /** @phpstan-ignore return.type */
        return $this->entities[$entity];
    }

    /**
     * @param ReflectionClass<EntityInterface> $class
     *
     * @return iterable<string, Field|Join>
     */
    private function fields(ReflectionClass $class): iterable
    {
        $constructor = $class->getConstructor();

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

            foreach ($property->getAttributes() as $attribute) {
                $annotation = $attribute->newInstance();
                if ($annotation instanceof JoinInterface === false) {
                    continue;
                }

//                var_export([
//                    $propertyName,
//                    $annotation,
//                    $property->getType(),
//                ]);
                $joinEntity   = '';
                $propertyType = $property->getType();
                if ($propertyType instanceof ReflectionNamedType) {
                    $joinEntity = $propertyType->getName();
                }

                if (in_array($joinEntity, ['array', 'iterable', 'list'], true)) {
                    $joinEntity = new NaivePropertyTypeResolver()->typeFromConstructorParameter($property, $constructor)->concreteTypes()[0]->name;
//                    var_export([
//                        $propertyName,
//                        $annotation,
//                        $property->getType(),
//                        new NaivePropertyTypeResolver()->typeFromConstructorParameter($property, $constructor),
//                    ]);
                }

//                var_export([
//                    $propertyName,
//                    $annotation,
//                    $property->getType(),
//                    $joinEntity,
//                ]);
                if (class_exists($joinEntity) && is_subclass_of($joinEntity, EntityInterface::class)) {
                    foreach ($this->join($property, $annotation, $joinEntity) as $join) {
                        yield $join->property => $join;

                        if ($join->type === JointType::LEFT) {
                            continue;
                        }

                        foreach ($join->clause as $clause) {
                            yield $clause->localKey => new Field(
                                $clause->localKey,
                                $clause->localKey,
                                (static function (ReflectionProperty $property, string $joinEntity): string {
                                    $type = $property->getType();
                                    if ($type !== null) {
                                        return str_replace($joinEntity, 'string', (string) $type);
                                    }

                                    return 'string';
                                })($roaveProperty, $joinEntity),
                            );
                        }
                    }
                }

                continue 2;
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
     * @param class-string<T> $entity
     *
     * @return iterable<string, Join>
     *
     * @template T of EntityInterface
     */
    private function join(\ReflectionProperty $property, JoinInterface $join, string $entity): iterable
    {
        yield $property->name => new Join(
            new ReflectionClass(
                InspectedEntity::class,
            )->newLazyProxy(
                fn (): InspectedEntityInterface => $this->entity($entity),
            ),
            /** @phpstan-ignore argument.type,property.notFound */
            $join->type,
            $property->name,
            StaticInflector::underscore($property->name),
            /** @phpstan-ignore argument.type,property.notFound */
            $join->lazy,
            /** @phpstan-ignore argument.type,argument.unpackNonIterable,property.notFound */
            ...$join->clause,
        );
    }
}
