<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use WyriHaximus\React\SimpleORM\Annotation\Table;

/**
 * @Table("tables")
 */
class EntityStub
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
