<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use RuntimeException;
use WyriHaximus\React\SimpleORM\Annotation\JoinInterface;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

use function array_key_exists;
use function current;
use function method_exists;
use function WyriHaximus\iteratorOrArrayToArray;

final class EntityInspector
{
    private Configuration $configuration;
    private Reader $annotationReader;

    /** @var InspectedEntityInterface[] */
    private array $entities = [];

    public function __construct(Configuration $configuration, Reader $annotationReader)
    {
        $this->configuration    = $configuration;
        $this->annotationReader = $annotationReader;
    }

    public function entity(string $entity): InspectedEntityInterface
    {
        if (! array_key_exists($entity, $this->entities)) {
            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress ArgumentTypeCoercion
             */
            $class           = new ReflectionClass($entity);
            $tableAnnotation = $this->annotationReader->getClassAnnotation($class, Table::class);

            if ($tableAnnotation instanceof Table === false) {
                throw new RuntimeException('Missing Table annotation on entity: ' . $entity);
            }

            /**
             * @phpstan-ignore-next-line
             * @psalm-suppress ArgumentTypeCoercion
             */
            $joins = iteratorOrArrayToArray($this->joins($class));
            /** @psalm-suppress ArgumentTypeCoercion */
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $this->configuration->tablePrefix() . $tableAnnotation->table(),
                iteratorOrArrayToArray($this->fields($class, $joins)), /** @phpstan-ignore-line */
                $joins
            );
        }

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

            $roaveProperty = (static function (BetterReflection $br, string $class): \Roave\BetterReflection\Reflection\ReflectionClass {
                if (method_exists($br, 'classReflector')) {
                    return $br->classReflector()->reflect($class);
                }

                return $br->reflector()->reflectClass($class);
            })(new BetterReflection(), $class->getName())->getProperty($property->getName());

            if ($roaveProperty === null) {
                continue;
            }

            /** @psalm-suppress PossiblyNullReference */
            yield $property->getName() => new Field(
                $property->getName(),
                (static function (ReflectionProperty $property): string {
                    $type = $property->getType();
                    if ($type !== null) {
                        return (string) $type;
                    }

                    return (string) current($property->getDocBlockTypes());
                })($roaveProperty)
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
        $annotations = $this->annotationReader->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof JoinInterface === false) {
                continue;
            }

            yield $annotation->property() => new Join(
                new LazyInspectedEntity($this, $annotation->entity()),
                $annotation->type(),
                $annotation->property(),
                $annotation->lazy(),
                ...$annotation->clause()
            );
        }
    }
}
