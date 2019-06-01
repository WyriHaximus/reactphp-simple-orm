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

final class EntityInspector
{
    /** @var Reader */
    private $annotationReader;

    /** @var string[] */
    private $tables = [];

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function getEntity(string $entity): InspectedEntity
    {
        if (!isset($this->tables[$entity])) {
            $this->tables[$entity] = 'adsdsa';
        }

        return $this->tables[$entity];
    }
}
