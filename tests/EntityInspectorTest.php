<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\DevApp\React\SimpleORM\BlogPostStub;
use WyriHaximus\DevApp\React\SimpleORM\CommentStub;
use WyriHaximus\DevApp\React\SimpleORM\UserStub;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;

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
        self::assertSame('string', $fields['id']->type);
        self::assertArrayHasKey('name', $fields);
        self::assertSame('string', $fields['name']->type);
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
                'previous_blog_post_id' => 'string|null',
                'next_blog_post_id' => 'string|null',
                'author_id' => 'string',
                'publisher_id' => 'string',
                'title' => 'string',
                'contents' => 'string',
                'views' => 'int',
                'created' => 'DateTimeImmutable',
                'modified' => 'DateTimeImmutable',
            ] as $key => $type
        ) {
            self::assertArrayHasKey($key, $fields, $key);
            self::assertSame($type, $fields[$key]->type, $key);
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
}
