<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Entity;

final class Field
{
    /** @var string */
    private $name;

    /** @var string */
    private $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
