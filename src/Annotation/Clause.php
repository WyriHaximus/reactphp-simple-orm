<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use function property_exists;

/**
 * @Annotation
 * @Target("ANNOTATION")
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Clause
{
    //phpcs:disable
    private string $local_key;

    private ?string $local_cast = null;

    private ?string $local_function = null;

    private string $foreign_key;

    private ?string $foreign_cast = null;

    private ?string $foreign_function = null;
    //phpcs:enable

    /**
     * @param string[]|null[] $clause
     */
    public function __construct(array $clause)
    {
        /** @psalm-suppress RawObjectIteration */
        foreach ($clause as $name => $value) {
            if (! property_exists($this, $name)) {
                continue;
            }

            $this->$name = $clause[$name];
        }
    }

    public function getLocalKey(): string
    {
        //phpcs:disable
        return $this->local_key;
        //phpcs:enable
    }

    public function getForeignKey(): string
    {
        //phpcs:disable
        return $this->foreign_key;
        //phpcs:enable
    }

    public function getLocalCast(): ?string
    {
        //phpcs:disable
        return $this->local_cast;
        //phpcs:enable
    }

    public function getForeignCast(): ?string
    {
        //phpcs:disable
        return $this->foreign_cast;
        //phpcs:enable
    }

    public function getLocalFunction(): ?string
    {
        //phpcs:disable
        return $this->local_function;
        //phpcs:enable
    }

    public function getForeignFunction(): ?string
    {
        //phpcs:disable
        return $this->foreign_function;
        //phpcs:enable
    }
}
