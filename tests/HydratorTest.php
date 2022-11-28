<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\Hydrator;
use WyriHaximus\React\Tests\SimpleORM\Stub\BlogPostStub;
use WyriHaximus\React\Tests\SimpleORM\Stub\UserStub;
use WyriHaximus\TestUtilities\TestCase;

use function ApiClients\Tools\Rx\observableFromArray;
use function assert;
use function bin2hex;
use function random_bytes;
use function React\Promise\resolve;
use function Safe\date;

/**
 * @internal
 */
final class HydratorTest extends TestCase
{
    public function testHydrate(): void
    {
        $id    = '03450173-fef3-42c0-83c4-dfcfa4a474ee';
        $title = 'tables.title';

        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(UserStub::class),
            [
                'id' => $id,
                'name' => $title,
                'zelf' => resolve(true),
            ]
        );
        assert($entity instanceof UserStub);

        self::assertSame($id, $entity->id());
        self::assertSame($title, $entity->getName());
    }

    public function testHydrateIgnoringNonExistingFields(): void
    {
        $id    = '03450173-fef3-42c0-83c4-dfcfa4a474ee';
        $title = 'tables.title';

        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(UserStub::class),
            [
                'doesnotexist' => resolve(true),
                'id' => $id,
                'name' => $title,
                'zelf' => resolve(true),
                'alsodoesnotexist' => resolve(true),
            ]
        );
        assert($entity instanceof UserStub);

        self::assertSame($id, $entity->id());
        self::assertSame($title, $entity->getName());
    }

    public function testHydrateWithJoins(): void
    {
        $id            = '6bda4f06-4b7e-4cd5-b779-66a1b76187f9';
        $title         = 'null';
        $authorId      = 'dfc857d2-3564-4ed5-8a66-859158122169';
        $authorName    = 'llun';
        $publisherId   = 'a3fc1993-0930-4a9d-a2ad-3bf3a15ecee0';
        $publisherName = 'dasdsadas';

        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(BlogPostStub::class),
            [
                'doesnotexist' => resolve(true),
                'id' => $id,
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'contents' => bin2hex(random_bytes(133)),
                'views' => '133',
                'created' => date('Y-m-d H:i:s e'),
                'modified' => date('Y-m-d H:i:s e'),
                'previous_blog_post' => resolve(null),
                'next_blog_post' => resolve(null),
                'title' => $title,
                'author' => [
                    'id' => $authorId,
                    'name' => $authorName,
                    'zelf' => resolve(true),
                ],
                'publisher' => [
                    'id' => $publisherId,
                    'name' => $publisherName,
                    'zelf' => resolve(true),
                ],
                'comments' => observableFromArray([]),
                'alsodoesnotexist' => resolve(true),
            ]
        );
        assert($entity instanceof BlogPostStub);

        self::assertSame($id, $entity->id());
        self::assertSame($title, $entity->getTitle());
        self::assertSame($authorId, $entity->getAuthor()->id());
        self::assertSame($authorName, $entity->getAuthor()->getName());
        self::assertSame($publisherId, $entity->getPublisher()->id());
        self::assertSame($publisherName, $entity->getPublisher()->getName());
        self::assertSame(133, $entity->getViews());
    }

    public function testHydrateWithJoinsIgnoringNonExistingFields(): void
    {
        $id            = '6bda4f06-4b7e-4cd5-b779-66a1b76187f9';
        $title         = 'null';
        $authorId      = 'dfc857d2-3564-4ed5-8a66-859158122169';
        $authorName    = 'llun';
        $publisherId   = 'a3fc1993-0930-4a9d-a2ad-3bf3a15ecee0';
        $publisherName = 'dasdsadas';

        $entity = (new Hydrator())->hydrate(
            (new EntityInspector(new Configuration(''), new AnnotationReader()))->entity(BlogPostStub::class),
            [
                'id' => $id,
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'contents' => bin2hex(random_bytes(133)),
                'views' => '133',
                'created' => date('Y-m-d H:i:s e'),
                'modified' => date('Y-m-d H:i:s e'),
                'previous_blog_post' => resolve(null),
                'next_blog_post' => resolve(null),
                'title' => $title,
                'author' => [
                    'id' => $authorId,
                    'name' => $authorName,
                    'zelf' => resolve(true),
                ],
                'publisher' => [
                    'id' => $publisherId,
                    'name' => $publisherName,
                    'zelf' => resolve(true),
                ],
                'comments' => observableFromArray([]),
            ]
        );
        assert($entity instanceof BlogPostStub);

        self::assertSame($id, $entity->id());
        self::assertSame($title, $entity->getTitle());
        self::assertSame($authorId, $entity->getAuthor()->id());
        self::assertSame($authorName, $entity->getAuthor()->getName());
        self::assertSame($publisherId, $entity->getPublisher()->id());
        self::assertSame($publisherName, $entity->getPublisher()->getName());
        self::assertSame(133, $entity->getViews());
    }
}
