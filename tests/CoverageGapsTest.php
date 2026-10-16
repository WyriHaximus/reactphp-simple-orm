<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Latitude\QueryBuilder\Engine\PostgresEngine;
use Latitude\QueryBuilder\QueryFactory;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use stdClass;
use WyriHaximus\DevApp\React\SimpleORM\UserStub;
use WyriHaximus\React\SimpleORM\Attribute\Clause;
use WyriHaximus\React\SimpleORM\Attribute\InnerJoin;
use WyriHaximus\React\SimpleORM\Attribute\LeftJoin;
use WyriHaximus\React\SimpleORM\Attribute\Table;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Generated\Hydrator as GeneratedHydrator;
use WyriHaximus\React\SimpleORM\Query\Order\Asc;
use WyriHaximus\React\SimpleORM\Query\Where\Expression as WhereExpression;
use WyriHaximus\React\Tests\SimpleORM\Fixtures\RuntimeInspectBlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Fixtures\RuntimeInspectBlogPostWithCommentsStub;
use WyriHaximus\React\Tests\SimpleORM\Fixtures\RuntimeInspectUserStub;
use WyriHaximus\TestUtilities\TestCase;

use function Latitude\QueryBuilder\field;

final class CoverageGapsTest extends TestCase
{
    #[Test]
    public function tableAttribute(): void
    {
        self::assertSame('posts', new Table('posts')->table);
    }

    #[Test]
    public function orderAsc(): void
    {
        $asc = new Asc('name');

        self::assertSame('name', $asc->field());
        self::assertSame('ASC', $asc->order());
    }

    #[Test]
    public function whereExpression(): void
    {
        $select  = new QueryFactory()->select()->from('users');
        $inner   = (new QueryFactory()->select()->from('users')->where(field('id')->eq('1')))->asExpression();
        $expr    = new WhereExpression($inner, 'eq', ['1']);
        $applied = $expr->applyExpression($select->asExpression());

        self::assertSame(['1'], $applied->params(new PostgresEngine()));
    }

    #[Test]
    public function joinSerializeNonObject(): void
    {
        $mapper = new GeneratedHydrator();

        $inner = new InnerJoin([new Clause('local', 'foreign')]);
        $left  = new LeftJoin([new Clause('local', 'foreign')]);

        self::assertNull($inner->serialize('not-an-object', $mapper));
        self::assertNull($left->serialize('not-an-object', $mapper));
        self::assertNull($inner->serialize(new stdClass(), $mapper));
        self::assertNull($left->serialize(new stdClass(), $mapper));
    }

    #[Test]
    public function joinSerializeEntity(): void
    {
        $mapper = new GeneratedHydrator();

        $inner = new InnerJoin([new Clause('local', 'foreign')]);
        $left  = new LeftJoin([new Clause('local', 'foreign')]);
        $user  = new UserStub('id', 'name');

        self::assertIsArray($inner->serialize($user, $mapper));
        self::assertIsArray($left->serialize($user, $mapper));
    }

    #[Test]
    public function leftJoinSerializeLazyObject(): void
    {
        $mapper = new GeneratedHydrator();
        $left   = new LeftJoin([new Clause('local', 'foreign')]);
        $lazy   = new ReflectionClass(UserStub::class)->newLazyProxy(static fn (): UserStub => new UserStub('id', 'name'));

        self::assertNull($left->serialize($lazy, $mapper));
    }

    #[Test]
    public function entityInspectorRuntimeEntity(): void
    {
        $inspected = new EntityInspector(new Configuration(''))->entity(RuntimeInspectUserStub::class);

        self::assertSame(RuntimeInspectUserStub::class, $inspected->class());
        self::assertSame('runtime_inspect_users', $inspected->table());
        self::assertArrayHasKey('id', $inspected->fields());
        self::assertSame([], $inspected->joins());
    }

    #[Test]
    public function entityInspectorRuntimeEntityWithJoin(): void
    {
        $inspected = new EntityInspector(new Configuration(''))->entity(RuntimeInspectBlogPostStub::class);

        self::assertArrayHasKey('author', $inspected->joins());
        self::assertSame(RuntimeInspectUserStub::class, $inspected->joins()['author']->entity->class());
    }

    #[Test]
    public function entityInspectorRuntimeEntityWithIterableJoin(): void
    {
        $inspected = new EntityInspector(new Configuration(''))->entity(RuntimeInspectBlogPostWithCommentsStub::class);

        self::assertArrayHasKey('comments', $inspected->joins());
        self::assertArrayHasKey('id', $inspected->fields());
    }
}
