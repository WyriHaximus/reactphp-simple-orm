<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class InnerJoin implements JoinInterface
{
    /** @var string */
    private $entity;

    /** @var string */
    private $local_key;

    /** @var string */
    private $local_cast;

    /** @var string */
    private $foreign_key;

    /** @var string */
    private $foreign_cast;

    /** @var string */
    private $property;

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
        return 'inner';
    }

    public function getLocalKey(): string
    {
        return $this->local_key;
    }

    public function getForeignKey(): string
    {
        return $this->foreign_key;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getLocalCast(): ?string
    {
        return $this->local_cast;
    }

    public function getForeignCast(): ?string
    {
        return $this->foreign_cast;
    }
}
