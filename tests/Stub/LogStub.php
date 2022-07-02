<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM\Stub;

use WyriHaximus\React\SimpleORM\Annotation\Table;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Tools\WithFieldsTrait;

/**
 * @Table("logs")
 */
final class LogStub implements EntityInterface
{
    use WithFieldsTrait;

    protected string $id;

    protected string $message;

    public function id(): string
    {
        return $this->id;
    }

    public function message(): string
    {
        return $this->message;
    }
}
