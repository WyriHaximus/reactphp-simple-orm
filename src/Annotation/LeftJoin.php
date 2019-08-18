<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class LeftJoin implements JoinInterface
{
    /** @var string */
    private $entity;

    /** @var Clause[] */
    private $clause;

    /** @var string */
    private $property;

    /** @var bool */
    private $lazy = self::IS_NOT_LAZY;

    public function __construct(array $table)
    {
        /** @psalm-suppress RawObjectIteration */
        foreach ($this as $name => $value) {
            if (isset($table[$name])) {
                $this->$name = $table[$name];
            }
        }
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getType(): string
    {
        return 'left';
    }

    public function getLazy(): bool
    {
        return $this->lazy;
    }

    /**
     * @return Clause[]
     */
    public function getClause(): array
    {
        return $this->clause;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
