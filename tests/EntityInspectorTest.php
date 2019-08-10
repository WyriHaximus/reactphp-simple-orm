<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

/**
 * @internal
 */
final class EntityInspectorTest extends AsyncTestCase
{
    /** @var EntityInspector */
    private $entityInspector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityInspector = new EntityInspector(new AnnotationReader());
    }

    /**
     * @test
     */
    public function inspect(): void
    {
        $inspectedEntity = $this->entityInspector->getEntity(UserStub::class);

        self::assertSame(UserStub::class, $inspectedEntity->getClass());
        self::assertSame('users', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(2, $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertSame('string', $fields['id']->getType());
        self::assertArrayHasKey('name', $fields);
        self::assertSame('string', $fields['name']->getType());
    }

    /**
     * @test
     */
    public function inspectWithJoins(): void
    {
        $inspectedEntity = $this->entityInspector->getEntity(BlogPostStub::class);

        self::assertSame(BlogPostStub::class, $inspectedEntity->getClass());
        self::assertSame('blog_posts', $inspectedEntity->getTable());

        $fields = $inspectedEntity->getFields();
        self::assertCount(8, $fields);

        foreach ([
            'id' => 'string',
            'author_id' => 'string',
            'title' => 'string',
            'contents' => 'string',
            'views' => 'int',
            'created' => '\DateTimeImmutable',
            'modified' => '\DateTimeImmutable',
        ] as $key => $type) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->getType(), $key);
        }

        $joins = $inspectedEntity->getJoins();
        self::assertCount(3, $joins);

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
        self::assertCount(2, $joins['comments']->getEntity()->getJoins()['author']->getEntity()->getFields());
        self::assertSame('author_id', $joins['comments']->getEntity()->getJoins()['author']->getLocalKey());
        self::assertNull($joins['comments']->getEntity()->getJoins()['author']->getLocalCast());
        self::assertSame('id', $joins['comments']->getEntity()->getJoins()['author']->getForeignKey());
        self::assertNull($joins['comments']->getEntity()->getJoins()['author']->getForeignCast());
        self::assertSame('author', $joins['comments']->getEntity()->getJoins()['author']->getProperty());
    }

    /**
     * @test
     */
    public function inspectWithoutTable(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Missing Table annotation on entity: ' . NoSQLStub::class);

        $this->entityInspector->getEntity(NoSQLStub::class);
    }
}
