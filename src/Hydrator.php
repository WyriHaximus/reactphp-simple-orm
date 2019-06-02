<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use GeneratedHydrator\Configuration;

final class Hydrator
{
    /** @var string[] */
    private $hydrators = [];

    /** @var callable[] */
    private $middleware = [];

    public function hydrate(InspectedEntity $inspectedEntity, array $data): object
    {
        $class = $inspectedEntity->getClass();
        if (!isset($this->hydrators[$class])) {
            $this->hydrators[$inspectedEntity->getClass()] = (function ($class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                return new $hydratorClass();
            })($class);
        }

        foreach ($inspectedEntity->getJoins() as $join) {
            if ($join->getProperty() !== null) {
                $data[$inspectedEntity->getTable()][$join->getProperty()] = $this->hydrate(
                    $join->getEntity(),
                    $data
                );
                unset($data[$inspectedEntity->getTable()][$join->getProperty()]);
            }
        }

        return $this->hydrators[$class]->hydrate($data[$inspectedEntity->getTable()], new $class());
    }
}
