<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

use function property_exists;

/**
 * @Annotation
 * @Target("CLASS")
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class LeftJoin implements JoinInterface
{
    private string $entity;

    /** @var Clause[] */
    private array $clause;

    private string $property;

    private bool $lazy = self::IS_NOT_LAZY;

    /**
     * @param string[]|array[]|bool[] $leftJoin
     */
    public function __construct(array $leftJoin)
    {
        /** @psalm-suppress RawObjectIteration */
        foreach ($leftJoin as $name => $value) {
            if (! property_exists($this, $name)) {
                continue;
            }

            $this->$name = $leftJoin[$name]; /** @phpstan-ignore-line */
        }
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function type(): string
    {
        return 'left';
    }

    public function lazy(): bool
    {
        return $this->lazy;
    }

    /**
     * @return Clause[]
     */
    public function clause(): array
    {
        return $this->clause;
    }

    public function property(): string
    {
        return $this->property;
    }
}
