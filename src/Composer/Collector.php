<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Composer;

use Roave\BetterReflection\Reflection\ReflectionClass;
use WyriHaximus\Composer\GenerativePluginTooling\Item as ItemContract;
use WyriHaximus\Composer\GenerativePluginTooling\ItemCollector;
use WyriHaximus\React\SimpleORM\EntityInterface;

use function assert;
use function is_subclass_of;

final class Collector implements ItemCollector
{
    /** @return iterable<ItemContract> */
    public function collect(ReflectionClass $class): iterable
    {
        $entityClass = $class->getName();
        assert(is_subclass_of($entityClass, EntityInterface::class));

        yield new Item($entityClass);
    }
}
