<?php declare(strict_types=1);

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
use function WyriHaximus\iteratorOrArrayToArray;

final class EntityInspector
{
    private Reader $annotationReader;

    /** @var InspectedEntityInterface[] */
    private array $entities = [];

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function getEntity(string $entity): InspectedEntityInterface
    {
        if (! array_key_exists($entity, $this->entities)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $class           = new ReflectionClass($entity);
            $tableAnnotation = $this->annotationReader->getClassAnnotation($class, Table::class);

            if ($tableAnnotation instanceof Table === false) {
                throw new RuntimeException('Missing Table annotation on entity: ' . $entity);
            }

            /** @psalm-suppress ArgumentTypeCoercion */
            $joins = iteratorOrArrayToArray($this->getJoins($class));
            /** @psalm-suppress ArgumentTypeCoercion */
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $tableAnnotation->getTable(),
                iteratorOrArrayToArray($this->getFields($class, $joins)),
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
    private function getFields(ReflectionClass $class, array $joins): iterable
    {
        foreach ($class->getProperties() as $property) {
            if (array_key_exists($property->getName(), $joins)) {
                continue;
            }

            $roaveProperty = (new BetterReflection())
                ->classReflector()
                ->reflect($class->getName())->getProperty($property->getName());

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
    private function getJoins(ReflectionClass $class): iterable
    {
        $annotations = $this->annotationReader->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof JoinInterface === false) {
                continue;
            }

            yield $annotation->getProperty() => new Join(
                new LazyInspectedEntity($this, $annotation->getEntity()),
                $annotation->getType(),
                $annotation->getProperty(),
                $annotation->getLazy(),
                ...$annotation->getClause()
            );
        }
    }
}
