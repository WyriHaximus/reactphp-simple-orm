<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\PHPSan;

use Override;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;
use WyriHaximus\React\SimpleORM\EntityInterface;

final class ShipMonkDeadCode extends ReflectionBasedMemberUsageProvider
{
    #[Override]
    public function shouldMarkMethodAsUsed(ReflectionMethod $method): VirtualUsageData|null
    {
        if ($method->getDeclaringClass()->implementsInterface(EntityInterface::class)) {
            return VirtualUsageData::withNote('Class is a ORM Entity (PSR-14 event dispatcher) Listener');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkPropertyAsRead(ReflectionProperty $property): VirtualUsageData|null
    {
        if ($property->getDeclaringClass()->implementsInterface(EntityInterface::class)) {
            return VirtualUsageData::withNote('ORM Entity property (hydrated/persisted via reflection)');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkPropertyAsWritten(ReflectionProperty $property): VirtualUsageData|null
    {
        if ($property->getDeclaringClass()->implementsInterface(EntityInterface::class)) {
            return VirtualUsageData::withNote('ORM Entity property (hydrated/persisted via reflection)');
        }

        return null;
    }
}
