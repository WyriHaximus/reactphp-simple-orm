<?php

declare(strict_types=1);

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

    /** @param string[]|null[] $clause */
    public function __construct(array $clause)
    {
        /** @psalm-suppress RawObjectIteration */
        foreach ($clause as $name => $value) {
            if (! property_exists($this, $name)) {
                continue;
            }

            $this->$name = $clause[$name]; /** @phpstan-ignore-line */
        }
    }

    public function localKey(): string
    {
        //phpcs:disable
        return $this->local_key;
        //phpcs:enable
    }

    public function foreignKey(): string
    {
        //phpcs:disable
        return $this->foreign_key;
        //phpcs:enable
    }

    /** @phpstan-ignore-next-line */
    public function localCast(): string|null
    {
        //phpcs:disable
        return $this->local_cast;
        //phpcs:enable
    }

    /** @phpstan-ignore-next-line */
    public function foreignCast(): string|null
    {
        //phpcs:disable
        return $this->foreign_cast;
        //phpcs:enable
    }

    /** @phpstan-ignore-next-line */
    public function localFunction(): string|null
    {
        //phpcs:disable
        return $this->local_function;
        //phpcs:enable
    }

    /** @phpstan-ignore-next-line */
    public function foreignFunction(): string|null
    {
        //phpcs:disable
        return $this->foreign_function;
        //phpcs:enable
    }
}
