<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use WyriHaximus\React\SimpleORM\Annotation\InnerJoin;
use WyriHaximus\React\SimpleORM\Annotation\LeftJoin;
use WyriHaximus\React\SimpleORM\Annotation\RightJoin;
use WyriHaximus\React\SimpleORM\Annotation\Table;

/**
 * @Table("table_with_joins")
 * @InnerJoin(
        entity=EntityStub::class,
        local_key="id",
        local_cast="VARCHAR",
        foreign_key="id",
        property="joined_entity"
 * )
 * @LeftJoin(
        entity=EntityStub::class,
        local_key="id",
        local_cast="BJIGINT",
        foreign_key="id",
        property="joined_entity"
 * )
 * @RightJoin(
        entity=EntityStub::class,
        local_key="id",
        foreign_key="id",
        foreign_cast="VARCHAR",
        property="joined_entity"
 * )
 */
class EntityWithJoinStub
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $foreign_id;

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
