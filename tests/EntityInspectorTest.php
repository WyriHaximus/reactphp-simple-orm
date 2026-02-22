<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
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

    #[Before]
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
        self::assertCount(12, $fields);

        foreach (
            [
                'id' => 'string',
                'previousBlogPostId' => 'string|null',
                'nextBlogPostId' => 'string|null',
                'authorId' => 'string',
                'publisherId' => 'string',
                'title' => 'string',
                'contents' => 'string',
                'views' => 'int',
                'created' => 'DateTimeImmutable',
                'modified' => 'DateTimeImmutable',
            ] as $key => $type
        ) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->type(), $key);
        }

        $joins = $inspectedEntity->joins();
        self::assertCount(5, $joins);

        self::assertArrayHasKey('author', $joins);
        self::assertSame(UserStub::class, $joins['author']->entity->class());
        $authorClause = current($joins['author']->clause);
        self::assertInstanceOf(Clause::class, $authorClause);
        self::assertSame('author_id', $authorClause->localKey);
        self::assertNull($authorClause->localCast);
        self::assertNull($authorClause->localFunction);
        self::assertSame('id', $authorClause->foreignKey);
        self::assertNull($authorClause->foreignCast);
        self::assertNull($authorClause->foreignFunction);
        self::assertSame('author', $joins['author']->property);

        self::assertSame(CommentStub::class, $joins['comments']->entity->class());
        $commentClause = current($joins['comments']->clause);
        self::assertInstanceOf(Clause::class, $commentClause);
        self::assertSame('id', $commentClause->localKey);
        self::assertSame('BIGINT', $commentClause->localCast);
        self::assertNull($commentClause->localFunction);
        self::assertSame('blog_post_id', $commentClause->foreignKey);
        self::assertNull($commentClause->foreignCast);
        self::assertNull($commentClause->foreignFunction);
        self::assertSame('comments', $joins['comments']->property);

        self::assertArrayHasKey('author', $joins['comments']->entity->joins());
        self::assertSame(UserStub::class, $joins['comments']->entity->joins()['author']->entity->class());
        self::assertCount(2, $joins['comments']->entity->joins()['author']->entity->fields());
        $commentAuthorClause = current($joins['comments']->entity->joins()['author']->clause);
        self::assertInstanceOf(Clause::class, $commentAuthorClause);
        self::assertSame('author_id', $commentAuthorClause->localKey);
        self::assertNull($commentAuthorClause->localCast);
        self::assertNull($commentAuthorClause->localFunction);
        self::assertSame('id', $commentAuthorClause->foreignKey);
        self::assertNull($commentAuthorClause->foreignCast);
        self::assertNull($commentAuthorClause->foreignFunction);
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
