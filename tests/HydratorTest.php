<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use WyriHaximus\React\SimpleORM\Hydrator;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class HydratorTest extends TestCase
{
    public function testHydrate(): void
    {
        $id = 123;
        $title = 'null';

        /** @var EntityStub $entity */
        $entity = (new Hydrator())->hydrate(EntityStub::class, [
            'id' => $id,
            'title' => $title,
        ]);

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
    }

    public function testHydrateWithJoins(): void
    {
        $id = 123;
        $title = 'null';

        /** @var EntityStub $entity */
        $entity = (new Hydrator())->hydrate(EntityWithJoinStub::class, [
            'id' => $id,
            'title' => $title,
        ]);

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
    }
}
