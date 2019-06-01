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
        property="joined_inner_entity"
 * )
 * @LeftJoin(
        entity=EntityStub::class,
        local_key="id",
        local_cast="BJIGINT",
        foreign_key="id",
        property="joined_left_entity"
 * )
 * @RightJoin(
        entity=EntityStub::class,
        local_key="id",
        foreign_key="id",
        foreign_cast="VARCHAR",
        property="joined_right_entity"
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

    /** @var EntityStub */
    protected $joined_inner_entity;

    /** @var EntityStub */
    protected $joined_left_entity;

    /** @var EntityStub */
    protected $joined_right_entity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getForeignId(): int
    {
        return $this->foreign_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getJoinedInnerEntity(): EntityStub
    {
        return $this->joined_inner_entity;
    }

    public function getJoinedLeftEntity(): EntityStub
    {
        return $this->joined_left_entity;
    }

    public function getJoinedRightEntity(): EntityStub
    {
        return $this->joined_right_entity;
    }
}
