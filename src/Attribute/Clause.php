<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Attribute;

/** @api */
final readonly class Clause
{
    /** @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullDefaultValue,ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullDefaultValue,ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullDefaultValue,ergebnis.noConstructorParameterWithDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullDefaultValue */
    public function __construct(
        public string $localKey,
        public string $foreignKey,
        public string|null $localCast = null,
        public string|null $localFunction = null,
        public string|null $foreignCast = null,
        public string|null $foreignFunction = null,
    ) {
    }

    /** @param array{localKey: string, foreignKey: string, localCast: string|null, localFunction: string|null, foreignCast: string|null, foreignFunction: string|null} $properties */
    public static function __set_state(array $properties): Clause
    {
        return new self(
            $properties['localKey'],
            $properties['foreignKey'],
            $properties['localCast'],
            $properties['localFunction'],
            $properties['foreignCast'],
            $properties['foreignFunction'],
        );
    }
}
