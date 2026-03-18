<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

use Attribute;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\PropertySerializer;
use ReflectionClass;
use WyriHaximus\React\SimpleORM\Entity\JointType;

/** @api */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class LeftJoin implements JoinInterface, PropertySerializer
{
    public JointType $type;

    /**
     * @param array<Clause> $clause
     *
     * @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue
     */
    public function __construct(
        public array $clause,
        public bool $lazy = self::IS_NOT_LAZY,
    ) {
        $this->type = JointType::LEFT;
    }

    public function serialize(mixed $value, ObjectMapper $hydrator): mixed
    {
        if (new ReflectionClass($value::class)->isUninitializedLazyObject($value)) {
            return null;
        }

        return $hydrator->serializeObject($value);
    }
}
