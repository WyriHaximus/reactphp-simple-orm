<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\SimpleORM;

use PHPUnit\Framework\Attributes\Test;
use Rx\Observable;
use WyriHaximus\DevApp\React\SimpleORM\BlogPostStub;
use WyriHaximus\DevApp\React\SimpleORM\UserStub;
use WyriHaximus\React\SimpleORM\Configuration;
use WyriHaximus\React\SimpleORM\EntityInspector;
use WyriHaximus\React\SimpleORM\EntityInterface;
use WyriHaximus\React\SimpleORM\Hydrator;
use WyriHaximus\TestUtilities\TestCase;

use function bin2hex;
use function date;
use function random_bytes;
use function React\Promise\resolve;
use function WyriHaximus\React\awaitObservable;

final class HydratorTest extends TestCase
{
    #[Test]
    public function hydrate(): void
    {
        $id    = '03450173-fef3-42c0-83c4-dfcfa4a474ee';
        $title = 'tables.title';

        $entity = new Hydrator()->hydrate(
            new EntityInspector(new Configuration(''))->entity(UserStub::class),
            [
                'id' => $id,
                'name' => $title,
            ],
        );

        self::assertSame($id, $entity->id);
        self::assertSame($title, $entity->name);
    }

    #[Test]
    public function hydrateIgnoringNonExistingFields(): void
    {
        $id    = '03450173-fef3-42c0-83c4-dfcfa4a474ee';
        $title = 'tables.title';

        $entity = new Hydrator()->hydrate(
            new EntityInspector(new Configuration(''))->entity(UserStub::class),
            [
                'id' => $id,
                'name' => $title,
            ],
        );

        self::assertSame($id, $entity->id);
        self::assertSame($title, $entity->name);
    }

    #[Test]
    public function hydrateWithJoins(): void
    {
        $id            = '6bda4f06-4b7e-4cd5-b779-66a1b76187f9';
        $title         = 'null';
        $authorId      = 'dfc857d2-3564-4ed5-8a66-859158122169';
        $authorName    = 'llun';
        $publisherId   = 'a3fc1993-0930-4a9d-a2ad-3bf3a15ecee0';
        $publisherName = 'dasdsadas';

        $entity = new Hydrator()->hydrate(
            new EntityInspector(new Configuration(''))->entity(BlogPostStub::class),
            [
                'id' => $id,
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'contents' => bin2hex(random_bytes(133)),
                'views' => 133,
                'created' => date('Y-m-d H:i:s e'),
                'modified' => date('Y-m-d H:i:s e'),
                'previous_blog_post' => resolve([
                    'id' => $id,
                    'author_id' => $authorId,
                    'publisher_id' => $publisherId,
                    'contents' => bin2hex(random_bytes(133)),
                    'views' => 133,
                    'created' => date('Y-m-d H:i:s e'),
                    'modified' => date('Y-m-d H:i:s e'),
                    'previous_blogost' => null,
                    'next_blog_post' => null,
                    'title' => $title,
                    'author' => [
                        'id' => $authorId,
                        'name' => $authorName,
                    ],
                    'publisher' => [
                        'id' => $publisherId,
                        'name' => $publisherName,
                    ],
                    'comments' => awaitObservable(Observable::fromArray([])),
                ]),
                'next_blog_post' => null,
                'title' => $title,
                'author' => [
                    'id' => $authorId,
                    'name' => $authorName,
                ],
                'publisher' => [
                    'id' => $publisherId,
                    'name' => $publisherName,
                ],
                'comments' => awaitObservable(Observable::fromArray([])),
            ],
        );

        foreach ([$entity, $entity->previousBlogPost] as $bp) {
            if (! $bp instanceof EntityInterface) {
                continue;
            }

            self::assertSame($id, $bp->id);
            self::assertSame($title, $bp->title);
            self::assertSame($authorId, $bp->author->id);
            self::assertSame($authorName, $bp->author->name);
            self::assertSame($publisherId, $bp->publisher->id);
            self::assertSame($publisherName, $bp->publisher->name);
            self::assertSame(133, $bp->views);
        }
//        self::assertNull($entity->nextBlogPost);
    }

    #[Test]
    public function hydrateWithJoinsIgnoringNonExistingFields(): void
    {
        $id            = '6bda4f06-4b7e-4cd5-b779-66a1b76187f9';
        $title         = 'null';
        $authorId      = 'dfc857d2-3564-4ed5-8a66-859158122169';
        $authorName    = 'llun';
        $publisherId   = 'a3fc1993-0930-4a9d-a2ad-3bf3a15ecee0';
        $publisherName = 'dasdsadas';

        $entity = new Hydrator()->hydrate(
            new EntityInspector(new Configuration(''))->entity(BlogPostStub::class),
            [
                'id' => $id,
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'contents' => bin2hex(random_bytes(133)),
                'views' => 133,
                'created' => date('Y-m-d H:i:s e'),
                'modified' => date('Y-m-d H:i:s e'),
                'previous_blog_post' => null,
                'next_blog_post' => null,
                'title' => $title,
                'author' => [
                    'id' => $authorId,
                    'name' => $authorName,
                ],
                'publisher' => [
                    'id' => $publisherId,
                    'name' => $publisherName,
                ],
                'comments' => awaitObservable(Observable::fromArray([])),
            ],
        );

        self::assertSame($id, $entity->id);
        self::assertSame($title, $entity->title);
        self::assertSame($authorId, $entity->author->id);
        self::assertSame($authorName, $entity->author->name);
        self::assertSame($publisherId, $entity->publisher->id);
        self::assertSame($publisherName, $entity->publisher->name);
        self::assertSame(133, $entity->views);
    }
}
