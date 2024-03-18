<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use RuntimeException;
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

        $this->entityInspector = new EntityInspector(new Configuration(''), new AnnotationReader());
    }

    /** @test */
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

    /** @test */
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
        self::assertSame(UserStub::class, $joins['author']->entity()->class());
        self::assertSame('author_id', current($joins['author']->clause())->localKey()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['author']->clause())->localCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['author']->clause())->localFunction()); /** @phpstan-ignore-line */
        self::assertSame('id', current($joins['author']->clause())->foreignKey()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['author']->clause())->foreignCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['author']->clause())->foreignFunction()); /** @phpstan-ignore-line */
        self::assertSame('author', $joins['author']->property());

        self::assertSame(CommentStub::class, $joins['comments']->entity()->class());
        self::assertSame('id', current($joins['comments']->clause())->localKey()); /** @phpstan-ignore-line */
        self::assertSame('BIGINT', current($joins['comments']->clause())->localCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->clause())->localFunction()); /** @phpstan-ignore-line */
        self::assertSame('blog_post_id', current($joins['comments']->clause())->foreignKey()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->clause())->foreignCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->clause())->foreignFunction()); /** @phpstan-ignore-line */
        self::assertSame('comments', $joins['comments']->property());

        self::assertArrayHasKey('author', $joins['comments']->entity()->joins());
        self::assertSame(UserStub::class, $joins['comments']->entity()->joins()['author']->entity()->class());
        self::assertCount(2, $joins['comments']->entity()->joins()['author']->entity()->fields());
        self::assertSame('author_id', current($joins['comments']->entity()->joins()['author']->clause())->localKey()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->entity()->joins()['author']->clause())->localCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->entity()->joins()['author']->clause())->localFunction()); /** @phpstan-ignore-line */
        self::assertSame('id', current($joins['comments']->entity()->joins()['author']->clause())->foreignKey()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->entity()->joins()['author']->clause())->foreignCast()); /** @phpstan-ignore-line */
        self::assertNull(current($joins['comments']->entity()->joins()['author']->clause())->foreignFunction()); /** @phpstan-ignore-line */
        self::assertSame('author', $joins['comments']->entity()->joins()['author']->property());
    }

    /** @test */
    public function inspectWithoutTable(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Missing Table annotation on entity: ' . NoSQLStub::class);

        $this->entityInspector->entity(NoSQLStub::class);
    }
}
