<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use GeneratedHydrator\Configuration;
use WyriHaximus\React\SimpleORM\Entity\Field;
use Zend\Hydrator\HydratorInterface;

final class Hydrator
{
    /** @var string[] */
    private $hydrators = [];

    /**
     * @param InspectedEntityInterface $inspectedEntity
     * @param mixed[] $data
     *
     * @return EntityInterface
     */
    public function hydrate(InspectedEntityInterface $inspectedEntity, array $data): EntityInterface
    {
        $class = $inspectedEntity->getClass();
        if (!array_key_exists($class, $this->hydrators)) {
            /**
             * @psalm-suppress MissingClosureReturnType
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            $this->hydrators[$inspectedEntity->getClass()] = (function (string $class) {
                $hydratorClass = (new Configuration($class))->createFactory()->getHydratorClass();

                /** @psalm-suppress InvalidStringClass */
                return new $hydratorClass();
            })($class);
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $inspectedEntity->getFields())) {
                $data[$key] = $this->castValueToCorrectType($inspectedEntity->getFields()[$key], $value);
            }
        }

        foreach ($inspectedEntity->getJoins() as $join) {
            if (\is_array($data[$join->getProperty()])) {
                $data[$join->getProperty()] = $this->hydrate(
                    $join->getEntity(),
                    $data[$join->getProperty()]
                );
            }
        }

        /**
         * @psalm-suppress PossiblyNullReference
         * @psalm-suppress InvalidMethodCall
         * @psalm-suppress InvalidStringClass
         */
        return $this->hydrators[$class]->hydrate($data, new $class());
    }

    /**
     * @param InspectedEntityInterface $inspectedEntity
     * @param EntityInterface $entity
     *
     * @return mixed[]
     */
    public function extract(InspectedEntityInterface $inspectedEntity, EntityInterface $entity): array
    {
        $class = $inspectedEntity->getClass();
        /** @var HydratorInterface $hydrator */
        $hydrator = $this->hydrators[$class];

        return $hydrator->extract($entity);
    }

    /**
     * @param Field $field
     * @param mixed $value
     *
     * @return mixed
     */
    private function castValueToCorrectType(Field $field, $value)
    {
        if ($field->getType() === 'int') {
            return (int) $value;
        }

        return $value;
    }
}
