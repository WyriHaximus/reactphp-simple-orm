<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

class HydrateStub
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
