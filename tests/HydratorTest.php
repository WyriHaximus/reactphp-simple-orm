<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use WyriHaximus\React\SimpleORM\EntityInspector;
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
        $title = 'tables.title';

        /** @var EntityStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(EntityStub::class),
            [
                'tables' => [
                    'id' => 123,
                    'title' => 'tables.title',
                ],
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
    }

    public function testHydrateWithJoins(): void
    {
        $id = 123;
        $title = 'null';

        /** @var EntityStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(EntityWithJoinStub::class),
            [
                'id' => $id,
                'title' => $title,
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
    }
}
