<?php declare(strict_types=1);

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
        $class = $inspectedEntity->getClass();
        if (! array_key_exists($class, $this->hydrators)) {
            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress InvalidPropertyAssignmentValue
             * @psalm-suppress PropertyTypeCoercion
             */
            $this->hydrators[$inspectedEntity->getClass()] = (static function (string $class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                /** @psalm-suppress InvalidStringClass */
                return new $hydratorClass();
            })($class);
        }

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, $inspectedEntity->getFields())) {
                continue;
            }

            $data[$key] = $this->castValueToCorrectType($inspectedEntity->getFields()[$key], $value);
        }

        foreach ($inspectedEntity->getJoins() as $join) {
            if (! is_array($data[$join->getProperty()])) {
                continue;
            }

            $data[$join->getProperty()] = $this->hydrate(
                $join->getEntity(),
                $data[$join->getProperty()]
            );
        }

        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress InvalidMethodCall
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress LessSpecificReturnStatement
         */
        return $this->hydrators[$class]->hydrate($data, new $class());
    }

    /**
     * @return mixed[]
     */
    public function extract(InspectedEntityInterface $inspectedEntity, EntityInterface $entity): array
    {
        $class    = $inspectedEntity->getClass();
        $hydrator = $this->hydrators[$class];

        return $hydrator->extract($entity);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function castValueToCorrectType(Field $field, $value)
    {
        if ($field->getType() === 'int') {
            return (int) $value;
        }

        if ($field->getType() === DateTimeImmutable::class) {
            return new DateTimeImmutable($value);
        }

        return $value;
    }
}
