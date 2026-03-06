<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use React\Promise\PromiseInterface;
use ReflectionClass;

use function array_key_exists;
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
            if (! array_key_exists($join->property, $data)) {
                continue;
            }

            if ($data[$join->property] instanceof PromiseInterface) {
                /** @var PromiseInterface<mixed> $promise */
                $promise               = $data[$join->property];
                $data[$join->property] = $this->createLazyProxy($join->entity, $promise);
                continue;
            }

            if (! is_array($data[$join->property])) {
                continue;
            }

            /** @var array<string, mixed> $joinData */
            $joinData              = $data[$join->property];
            $data[$join->property] = $this->hydrate(
                $join->entity,
                $joinData,
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

    /**
     * @param InspectedEntityInterface<T> $inspectedEntity
     * @param PromiseInterface<mixed>     $object
     *
     * @return T
     *
     * @template T of EntityInterface
     */
    private function createLazyProxy(InspectedEntityInterface $inspectedEntity, PromiseInterface $object): EntityInterface
    {
        /** @return T */
        return new ReflectionClass(
            $inspectedEntity->class(),
        )->newLazyProxy(
            function () use ($inspectedEntity, $object): EntityInterface {
                /** @var array<string, mixed> $data */
                $data = await($object);

                return $this->hydrate(
                    $inspectedEntity,
                    $data,
                );
            },
        );
    }
}
