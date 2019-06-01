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

final class InspectedEntity
{
    /** @var string */
    private $table;

    /** @var Field[] */
    private $fields = [];
}
