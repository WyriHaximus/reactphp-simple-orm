<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
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

        /** @var UserStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(UserStub::class),
            [
                'id' => $id,
                'name' => $title,
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getName());
    }

    public function testHydrateWithJoins(): void
    {
        $id = 123;
        $title = 'null';
        $authorId = 1;
        $authorName = 'llun';
        $publisherId = 2;
        $publisherName = 'dasdsadas';

        /** @var BlogPostStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            [
                'id' => $id,
                'title' => $title,
                'author' => [
                    'id' => $authorId,
                    'name' => $authorName,
                ],
                'publisher' => [
                    'id' => $publisherId,
                    'name' => $publisherName,
                ],
                'comments' => observableFromArray([]),
            ]
        );

        self::assertSame($id, $entity->getId());
        self::assertSame($title, $entity->getTitle());
        self::assertSame($authorId, $entity->getAuthor()->getId());
        self::assertSame($authorName, $entity->getAuthor()->getName());
        self::assertSame($publisherId, $entity->getPublisher()->getId());
        self::assertSame($publisherName, $entity->getPublisher()->getName());
    }
}
