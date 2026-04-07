<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

use Attribute;
use EventSauce\ObjectHydrator\DoNotSerialize;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\PropertyCaster;
use EventSauce\ObjectHydrator\PropertySerializer;
use ReflectionClass;
use WyriHaximus\React\SimpleORM\Entity\JointType;
use WyriHaximus\React\SimpleORM\EntityInterface;

/** @api */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class LeftJoin extends DoNotSerialize implements JoinInterface, PropertyCaster, PropertySerializer
{
    public readonly JointType $type;

    /**
     * @param array<Clause> $clause
     *
     * @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue
     */
    public function __construct(
        public readonly array $clause,
        public readonly bool $lazy = self::IS_NOT_LAZY,
    ) {
        $this->type = JointType::LEFT;
    }

    public function cast(mixed $value, ObjectMapper $hydrator): mixed
    {
        return $value;
    }

    public function serialize(mixed $value, ObjectMapper $hydrator): mixed
    {
        if (new ReflectionClass($value::class)->isUninitializedLazyObject($value)) {
            return null;
        }

        if ($value instanceof EntityInterface) {
            return $hydrator->serializeObject($value);
        }

        return null;
    }
}
