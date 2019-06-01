<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use ReflectionClass;
use ReflectionProperty;
use Rx\Observable;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\Entity\Field;

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
            $this->entities[$entity] = new InspectedEntity(
                $entity,
                $this->annotationReader->getClassAnnotation($class, Table::class)->getTable(),
                $this->getFields(),
            );
        }

        return $this->entities[$entity];
    }

    private function getFields(ReflectionClass $class): iterable
    {
        /** @var ReflectionProperty $property */
        foreach ($class->getProperties() as $property) {
            yield new Field($property->getName(), );
        }
    }
}
