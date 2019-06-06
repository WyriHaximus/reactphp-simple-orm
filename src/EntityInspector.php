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

    /** @var InspectedEntity[] */
    private $entities = [];

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function getEntity(string $entity): InspectedEntity
    {
        if (!isset($this->entities[$entity])) {
            $class = new ReflectionClass($entity);
            $joins = iteratorOrArrayToArray($this->getJoins($class));
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $this->annotationReader->getClassAnnotation($class, Table::class)->getTable(),
                iteratorOrArrayToArray($this->getFields($class, $joins)),
                $joins
            );
        }

        return $this->entities[$entity];
    }

    private function getFields(ReflectionClass $class, array $joins): iterable
    {
        /** @var ReflectionProperty $property */
        foreach ($class->getProperties() as $property) {
            if (isset($joins[$property->getName()])) {
                continue;
            }

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
                $this->getEntity($annotation->getEntity()),
                $annotation->getType(),
                $annotation->getLocalKey(),
                $annotation->getLocalCast(),
                $annotation->getForeignKey(),
                $annotation->getForeignCast(),
                $annotation->getProperty()
            );
        }
    }
}
