<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\CommentStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\NoSQLStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;

use function current;

final class EntityInspectorTest extends AsyncTestCase
{
    private EntityInspector $entityInspector;

    #[BeforeClass]
    protected function createEntityInspector(): void
    {
        $this->entityInspector = new EntityInspector(new Configuration(''));
    }

    #[Test]
    public function inspect(): void
    {
        $inspectedEntity = $this->entityInspector->entity(UserStub::class);

        self::assertSame(UserStub::class, $inspectedEntity->class());
        self::assertSame('users', $inspectedEntity->table());

        $fields = $inspectedEntity->fields();
        self::assertCount(2, $fields);
        self::assertArrayHasKey('id', $fields);
        self::assertSame('string', $fields['id']->type());
        self::assertArrayHasKey('name', $fields);
        self::assertSame('string', $fields['name']->type());
    }

    #[Test]
    public function inspectWithJoins(): void
    {
        $inspectedEntity = $this->entityInspector->entity(BlogPostStub::class);

        self::assertSame(BlogPostStub::class, $inspectedEntity->class());
        self::assertSame('blog_posts', $inspectedEntity->table());

        $fields = $inspectedEntity->fields();
        self::assertCount(10, $fields);

        foreach (
            [
                'id' => 'string',
                'previous_blog_post_id' => 'string',
                'next_blog_post_id' => 'string',
                'author_id' => 'string',
                'title' => 'string',
                'contents' => 'string',
                'views' => 'int',
                'created' => 'string',
                'modified' => 'string',
            ] as $key => $type
        ) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->type(), $key);
        }

        $joins = $inspectedEntity->joins();
        self::assertCount(5, $joins);

        self::assertArrayHasKey('author', $joins);
        self::assertSame(UserStub::class, $joins['author']->entity->class());
        self::assertSame('author_id', current($joins['author']->clause)->localKey);
        self::assertNull(current($joins['author']->clause)->localCast);
        self::assertNull(current($joins['author']->clause)->localFunction);
        self::assertSame('id', current($joins['author']->clause)->foreignKey);
        self::assertNull(current($joins['author']->clause)->foreignCast);
        self::assertNull(current($joins['author']->clause)->foreignFunction);
        self::assertSame('author', $joins['author']->property);

        self::assertSame(CommentStub::class, $joins['comments']->entity->class());
        self::assertSame('id', current($joins['comments']->clause)->localKey);
        self::assertSame('BIGINT', current($joins['comments']->clause)->localCast);
        self::assertNull(current($joins['comments']->clause)->localFunction);
        self::assertSame('blog_post_id', current($joins['comments']->clause)->foreignKey);
        self::assertNull(current($joins['comments']->clause)->foreignCast);
        self::assertNull(current($joins['comments']->clause)->foreignFunction);
        self::assertSame('comments', $joins['comments']->property);

        self::assertArrayHasKey('author', $joins['comments']->entity->joins());
        self::assertSame(UserStub::class, $joins['comments']->entity->joins()['author']->entity->class());
        self::assertCount(2, $joins['comments']->entity->joins()['author']->entity->fields());
        self::assertSame('author_id', current($joins['comments']->entity->joins()['author']->clause)->localKey);
        self::assertNull(current($joins['comments']->entity->joins()['author']->clause)->localCast);
        self::assertNull(current($joins['comments']->entity->joins()['author']->clause)->localFunction);
        self::assertSame('id', current($joins['comments']->entity->joins()['author']->clause)->foreignKey);
        self::assertNull(current($joins['comments']->entity->joins()['author']->clause)->foreignCast);
        self::assertNull(current($joins['comments']->entity->joins()['author']->clause)->foreignFunction);
        self::assertSame('author', $joins['comments']->entity->joins()['author']->property);
    }

    #[Test]
    public function inspectWithoutTable(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Missing Table annotation on entity: ' . NoSQLStub::class);

        $this->entityInspector->entity(NoSQLStub::class);
    }
}
