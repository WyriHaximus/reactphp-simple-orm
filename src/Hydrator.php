<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use GeneratedHydrator\Configuration;
use ReflectionClass;
use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;

final class Hydrator
{
    /** @var string[] */
    private $hydrators = [];

    /** @var callable[] */
    private $middleware = [];

    public function hydrate(string $class, array $data): object
    {
        $table = (new AnnotationReader())->getClassAnnotation(new \ReflectionClass($class), Table::class)->getTable();

        if (!isset($this->hydrators[$class])) {
            $this->hydrators[$class] = (function ($class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                return new $hydratorClass();
            })($class);
        }

        $annotations = (new AnnotationReader())->getClassAnnotations(new ReflectionClass($class));

        /** @var InnerJoin|null $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof InnerJoin === false && $annotation instanceof LeftJoin === false  && $annotation instanceof RightJoin === false) {
                continue;
            }

            if ($annotation->getProperty() !== null) {
                $data[$table][$annotation->getProperty()] = $this->hydrate(
                    $annotation->getEntity(),
                    $data
                );
                unset($data[(new AnnotationReader())->getClassAnnotation(new \ReflectionClass($annotation->getEntity()), Table::class)->getTable()]);
            }
        }
var_export($data);
        return $this->hydrators[$class]->hydrate($data[$table], new $class());
    }
}
