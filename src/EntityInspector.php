<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionProperty;
use Roave\BetterReflection\BetterReflection;
use function WyriHaximus\iteratorOrArrayToArray;
use WyriHaximus\React\SimpleORM\Annotation\JoinInterface;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;
use WyriHaximus\React\SimpleORM\Entity\Join;

final class EntityInspector
{
    /** @var Reader */
    private $annotationReader;

    /** @var InspectedEntityInterface[] */
    private $entities = [];

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function getEntity(string $entity): InspectedEntityInterface
    {
        if (!array_key_exists($entity, $this->entities)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $class = new ReflectionClass($entity);
            $tableAnnotation = $this->annotationReader->getClassAnnotation($class, Table::class);

            if ($tableAnnotation instanceof Table === false) {
                throw new \RuntimeException('Missing Table annotation on entity: ' . $entity);
            }

            $joins = iteratorOrArrayToArray($this->getJoins($class));
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
     * @param ReflectionClass $class
     * @param Join[] $joins
     *
     * @return iterable
     */
    private function getFields(ReflectionClass $class, array $joins): iterable
    {
        /** @var ReflectionProperty $property */
        foreach ($class->getProperties() as $property) {
            if (array_key_exists($property->getName(), $joins)) {
                continue;
            }

            /** @psalm-suppress PossiblyNullReference */
            yield $property->getName() => new Field(
                $property->getName(),
                (string)\current((new BetterReflection())
                    ->classReflector()
                    ->reflect($class->getName())->getProperty($property->getName())->getDocBlockTypes())
            );
        }
    }

    private function getJoins(ReflectionClass $class): iterable
    {
        $annotations = $this->annotationReader->getClassAnnotations($class);

        /** @var object|JoinInterface $annotation */
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
