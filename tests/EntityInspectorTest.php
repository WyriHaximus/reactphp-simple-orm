<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use RuntimeException;
use Safe\DateTimeImmutable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;
use function current;

final class EntityInspectorTest extends AsyncTestCase
{
    private EntityInspector $entityInspector;

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
        self::assertCount(10, $fields);

        foreach ([
            'id' => 'string',
            'previous_blog_post_id' => 'string',
            'next_blog_post_id' => 'string',
            'author_id' => 'string',
            'title' => 'string',
            'contents' => 'string',
            'views' => 'int',
            'created' => DateTimeImmutable::class,
            'modified' => DateTimeImmutable::class,
        ] as $key => $type) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->getType(), $key);
        }

        $joins = $inspectedEntity->getJoins();
        self::assertCount(5, $joins);

        self::assertArrayHasKey('author', $joins);
        self::assertSame(UserStub::class, $joins['author']->getEntity()->getClass());
        self::assertSame('author_id', current($joins['author']->getClause())->getLocalKey());
        self::assertNull(current($joins['author']->getClause())->getLocalCast());
        self::assertNull(current($joins['author']->getClause())->getLocalFunction());
        self::assertSame('id', current($joins['author']->getClause())->getForeignKey());
        self::assertNull(current($joins['author']->getClause())->getForeignCast());
        self::assertNull(current($joins['author']->getClause())->getForeignFunction());
        self::assertSame('author', $joins['author']->getProperty());

        self::assertSame(CommentStub::class, $joins['comments']->getEntity()->getClass());
        self::assertSame('id', current($joins['comments']->getClause())->getLocalKey());
        self::assertSame('BIGINT', current($joins['comments']->getClause())->getLocalCast());
        self::assertNull(current($joins['comments']->getClause())->getLocalFunction());
        self::assertSame('blog_post_id', current($joins['comments']->getClause())->getForeignKey());
        self::assertNull(current($joins['comments']->getClause())->getForeignCast());
        self::assertNull(current($joins['comments']->getClause())->getForeignFunction());
        self::assertSame('comments', $joins['comments']->getProperty());

        self::assertArrayHasKey('author', $joins['comments']->getEntity()->getJoins());
        self::assertSame(UserStub::class, $joins['comments']->getEntity()->getJoins()['author']->getEntity()->getClass());
        self::assertCount(2, $joins['comments']->getEntity()->getJoins()['author']->getEntity()->getFields());
        self::assertSame('author_id', current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getLocalKey());
        self::assertNull(current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getLocalCast());
        self::assertNull(current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getLocalFunction());
        self::assertSame('id', current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getForeignKey());
        self::assertNull(current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getForeignCast());
        self::assertNull(current($joins['comments']->getEntity()->getJoins()['author']->getClause())->getForeignFunction());
        self::assertSame('author', $joins['comments']->getEntity()->getJoins()['author']->getProperty());
    }

    /**
     * @test
     */
    public function inspectWithoutTable(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Missing Table annotation on entity: ' . NoSQLStub::class);

        $this->entityInspector->getEntity(NoSQLStub::class);
    }
}
