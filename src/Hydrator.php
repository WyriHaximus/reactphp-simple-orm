<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use React\Promise\PromiseInterface;
use ReflectionClass;

use function is_array;
use function React\Async\await;

final readonly class Hydrator
{
    private ObjectMapperUsingReflection $fallbackMapper;

    public function __construct()
    {
        $this->fallbackMapper = new ObjectMapperUsingReflection();
    }

    /**
     * @param array<string, mixed>        $data
     * @param InspectedEntityInterface<T> $inspectedEntity
     *
     * @return T
     *
     * @template T of EntityInterface
     */
    public function hydrate(InspectedEntityInterface $inspectedEntity, array $data): EntityInterface
    {
        foreach ($inspectedEntity->joins() as $join) {
            if ($data[$join->property] instanceof PromiseInterface) {
                $data[$join->property] = $this->createLazyProxy($join->entity, $data[$join->property]);
                continue;
            }

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
        /** @phpstan-ignore return.type */
        return $this->fallbackMapper->serializeObject($entity);
    }

    private function createLazyProxy(InspectedEntityInterface $inspectedEntity, PromiseInterface $object): EntityInterface
    {
        return new ReflectionClass(
            $inspectedEntity->class(),
        )->newLazyProxy(
            fn (): EntityInterface => $this->hydrate(
                $inspectedEntity,
                await($object),
            ),
        );
    }
}
