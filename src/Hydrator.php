<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use GeneratedHydrator\Configuration;

final class Hydrator
{
    /** @var string[] */
    private $hydrators = [];

    /** @var callable[] */
    private $middleware = [];

    public function hydrate(InspectedEntity $inspectedEntity, array $data): EntityInterface
    {
        $class = $inspectedEntity->getClass();
        if (!isset($this->hydrators[$class])) {
            $this->hydrators[$inspectedEntity->getClass()] = (function ($class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                return new $hydratorClass();
            })($class);
        }

        foreach ($inspectedEntity->getJoins() as $join) {
            if ($join->getProperty() !== null && is_array($data[$join->getProperty()])) {
                $data[$join->getProperty()] = $this->hydrate(
                    $join->getEntity(),
                    $data[$join->getProperty()]
                );
            }
        }

        return $this->hydrators[$class]->hydrate($data, new $class());
    }
}
