<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use GeneratedHydrator\Configuration;

final class Hydrator
{
    /**
     * @var string[]
     */
    private $hydrators = [];

    public function hydrate(string $class, array $data): object
    {
        if (!isset($this->hydrators[$class])) {
            $this->hydrators[$class] = (function ($class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                return new $hydratorClass();
            })($class);
        }

        return $this->hydrators[$class]->hydrate($data, new $class());
    }
}
