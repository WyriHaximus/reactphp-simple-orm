<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;

use function is_array;

final readonly class Hydrator
{
    private ObjectMapperUsingReflection $fallbackMapper;

    public function __construct()
    {
        $this->fallbackMapper = new ObjectMapperUsingReflection();
    }

    /** @param array<string, mixed> $data */
    public function hydrate(InspectedEntityInterface $inspectedEntity, array $data): EntityInterface
    {
        foreach ($inspectedEntity->joins() as $join) {
            if (! is_array($data[$join->property])) {
                continue;
            }

            $data[$join->property] = $this->hydrate(
                $join->entity,
                $data[$join->property],
            );
        }

        return $this->fallbackMapper->hydrateObject($inspectedEntity->class(), $data);
    }

    /** @return array<string, mixed> */
    public function extract(EntityInterface $entity): array
    {
        return $this->fallbackMapper->serializeObject($entity);
    }
}
