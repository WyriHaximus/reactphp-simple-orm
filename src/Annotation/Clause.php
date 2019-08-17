<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ANNOTATION")
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Clause
{
    /** @var string */
    private $local_key;

    /** @var string|null */
    private $local_cast;

    /** @var string|null */
    private $local_function;

    /** @var string */
    private $foreign_key;

    /** @var string|null */
    private $foreign_cast;

    /** @var string|null */
    private $foreign_function;

    public function __construct(array $table)
    {
        /** @psalm-suppress RawObjectIteration */
        foreach ($this as $name => $value) {
            if (isset($table[$name])) {
                $this->$name = $table[$name];
            }
        }
    }

    public function getLocalKey(): string
    {
        return $this->local_key;
    }

    public function getForeignKey(): string
    {
        return $this->foreign_key;
    }

    public function getLocalCast(): ?string
    {
        return $this->local_cast;
    }

    public function getForeignCast(): ?string
    {
        return $this->foreign_cast;
    }

    public function getLocalFunction(): ?string
    {
        return $this->local_function;
    }

    public function getForeignFunction(): ?string
    {
        return $this->foreign_function;
    }
}
