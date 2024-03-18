<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use GeneratedHydrator\Configuration;
use Safe\DateTimeImmutable;
use WyriHaximus\React\SimpleORM\Entity\Field;
use Zend\Hydrator\HydratorInterface;

use function array_key_exists;
use function is_array;

final class Hydrator
{
    /** @var array<string, HydratorInterface> */
    private array $hydrators = [];

    /**
     * @param mixed[] $data
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function hydrate(InspectedEntityInterface $inspectedEntity, array $data): EntityInterface
    {
        $class = $inspectedEntity->class();
        if (! array_key_exists($class, $this->hydrators)) {
            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @psalm-suppress PropertyTypeCoercion
             */
            $this->hydrators[$inspectedEntity->class()] = (static function (string $class): object {
                /**
                 * @phpstan-ignore-next-line
                 * @psalm-suppress ArgumentTypeCoercion
                 */
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                /** @psalm-suppress InvalidStringClass */
                return new $hydratorClass();
            })($class);
        }

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, $inspectedEntity->fields())) {
                continue;
            }

            $data[$key] = $this->castValueToCorrectType($inspectedEntity->fields()[$key], $value);
        }

        foreach ($inspectedEntity->joins() as $join) {
            if (! is_array($data[$join->property()])) {
                continue;
            }

            $data[$join->property()] = $this->hydrate(
                $join->entity(),
                $data[$join->property()],
            );
        }

        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress InvalidMethodCall
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress LessSpecificReturnStatement
         * @phpstan-ignore-next-line
         */
        return $this->hydrators[$class]->hydrate($data, new $class());
    }

    /** @return mixed[] */
    public function extract(InspectedEntityInterface $inspectedEntity, EntityInterface $entity): array
    {
        $class    = $inspectedEntity->class();
        $hydrator = $this->hydrators[$class];

        return $hydrator->extract($entity);
    }

    private function castValueToCorrectType(Field $field, mixed $value): mixed
    {
        if ($field->type() === 'int') {
            return (int) $value;
        }

        if ($field->type() === DateTimeImmutable::class) {
            return new DateTimeImmutable($value);
        }

        return $value;
    }
}
