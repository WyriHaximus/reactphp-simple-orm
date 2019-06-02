<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
use Doctrine\Common\Annotations\AnnotationReader;
use Plasma\SQL\QueryBuilder;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\ClientInterface;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Repository;

/**
 * @internal
 */
final class EntityInspectorTest extends AsyncTestCase
{
    /** @var EntityInspector */
    private $entityInspector;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityInspector = new EntityInspector(new AnnotationReader());
    }

    /**
     * @test
     */
    public function inspect()
    {
        $inspectedEntity = $this->entityInspector->getEntity(EntityStub::class);

        self::assertSame(EntityStub::class, $inspectedEntity->getClass());
        self::assertSame('tables', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(2, $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertSame('int', $fields['id']->getType());
        self::assertArrayHasKey('title', $fields);
        self::assertSame('string', $fields['title']->getType());
    }
    /**
     * @test
     */
    public function inspectWithJoins()
    {
        $inspectedEntity = $this->entityInspector->getEntity(EntityWithJoinStub::class);

        self::assertSame(EntityWithJoinStub::class, $inspectedEntity->getClass());
        self::assertSame('table_with_joins', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(3, $fields);

        self::assertArrayHasKey('id', $fields);
        self::assertSame('int', $fields['id']->getType());

        self::assertArrayHasKey('foreign_id', $fields);
        self::assertSame('int', $fields['foreign_id']->getType());

        self::assertArrayHasKey('title', $fields);
        self::assertSame('string', $fields['title']->getType());

        $joins = $inspectedEntity->getJoins();
        self::assertCount(3, $joins);

        self::assertArrayHasKey('joined_inner_entity', $joins);
        self::assertSame(EntityStub::class, $joins['joined_inner_entity']->getEntity()->getClass());
        self::assertSame('id', $joins['joined_inner_entity']->getLocalKey());
        self::assertSame('VARCHAR', $joins['joined_inner_entity']->getLocalCast());
        self::assertSame('id', $joins['joined_inner_entity']->getForeignKey());
        self::assertNull($joins['joined_inner_entity']->getForeignCast());
        self::assertSame('joined_inner_entity', $joins['joined_inner_entity']->getProperty());

        self::assertSame(EntityStub::class, $joins['joined_right_entity']->getEntity()->getClass());
        self::assertSame('id', $joins['joined_right_entity']->getLocalKey());
        self::assertNull($joins['joined_right_entity']->getLocalCast());
        self::assertSame('id', $joins['joined_right_entity']->getForeignKey());
        self::assertSame('VARCHAR', $joins['joined_right_entity']->getForeignCast());
        self::assertSame('joined_right_entity', $joins['joined_right_entity']->getProperty());
    }
}
