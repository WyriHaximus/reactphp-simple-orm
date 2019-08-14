<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use function ApiClients\Tools\Rx\observableFromArray;
use Doctrine\Common\Annotations\AnnotationReader;
use function React\Promise\resolve;
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
        $id = '03450173-fef3-42c0-83c4-dfcfa4a474ee';
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
        $id = '6bda4f06-4b7e-4cd5-b779-66a1b76187f9';
        $title = 'null';
        $authorId = 'dfc857d2-3564-4ed5-8a66-859158122169';
        $authorName = 'llun';
        $publisherId = 'a3fc1993-0930-4a9d-a2ad-3bf3a15ecee0';
        $publisherName = 'dasdsadas';

        /** @var BlogPostStub $entity */
        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new AnnotationReader()))->getEntity(BlogPostStub::class),
            [
                'id' => $id,
                'previous_blog_post' => resolve(null),
                'next_blog_post' => resolve(null),
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
