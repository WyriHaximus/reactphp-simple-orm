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
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

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
        $inspectedEntity = $this->entityInspector->getEntity(UserStub::class);

        self::assertSame(UserStub::class, $inspectedEntity->getClass());
        self::assertSame('users', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(2, $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertSame('int', $fields['id']->getType());
        self::assertArrayHasKey('name', $fields);
        self::assertSame('string', $fields['name']->getType());
    }
    /**
     * @test
     */
    public function inspectWithJoins()
    {
        $inspectedEntity = $this->entityInspector->getEntity(BlogPostStub::class);

        self::assertSame(BlogPostStub::class, $inspectedEntity->getClass());
        self::assertSame('blog_posts', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(4, $fields);

        foreach ([
            'id' => 'int',
            'author_id' => 'int',
            'title' => 'string',
            'contents' => 'string',
        ] as $key => $type) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->getType(), $key);
        }

        $joins = $inspectedEntity->getJoins();
        self::assertCount(2, $joins);

        self::assertArrayHasKey('author', $joins);
        self::assertSame(UserStub::class, $joins['author']->getEntity()->getClass());
        self::assertSame('author_id', $joins['author']->getLocalKey());
        self::assertNull($joins['author']->getLocalCast());
        self::assertSame('id', $joins['author']->getForeignKey());
        self::assertNull($joins['author']->getForeignCast());
        self::assertSame('author', $joins['author']->getProperty());

        self::assertSame(CommentStub::class, $joins['comments']->getEntity()->getClass());
        self::assertSame('id', $joins['comments']->getLocalKey());
        self::assertSame('BIGINT', $joins['comments']->getLocalCast());
        self::assertSame('blog_post_id', $joins['comments']->getForeignKey());
        self::assertNull($joins['comments']->getForeignCast());
        self::assertSame('comments', $joins['comments']->getProperty());

        self::assertArrayHasKey('author', $joins['comments']->getEntity()->getJoins());
        self::assertSame(UserStub::class, $joins['comments']->getEntity()->getJoins()['author']->getEntity()->getClass());
        self::assertSame('author_id', $joins['comments']->getEntity()->getJoins()['author']->getLocalKey());
        self::assertNull($joins['comments']->getEntity()->getJoins()['author']->getLocalCast());
        self::assertSame('id', $joins['comments']->getEntity()->getJoins()['author']->getForeignKey());
        self::assertNull($joins['comments']->getEntity()->getJoins()['author']->getForeignCast());
        self::assertSame('author', $joins['comments']->getEntity()->getJoins()['author']->getProperty());

    }
}
