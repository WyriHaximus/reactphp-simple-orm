<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Hydrator;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;
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
            (new EntityInspector(new AnnotationReader()))->getEntity(UserStub::class),
            [
                'users' => [
                    'id' => 123,
                    'name' => 'tables.title',
                ],
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getName());
    }

    public function testHydrateWithJoins(): void
    {
        $id = 123;
        $title = 'null';

        /** @var EntityStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            [
                'id' => $id,
                'title' => $title,
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
    }
}
