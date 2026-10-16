<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Generated;

use EventSauce\ObjectHydrator\IterableList;
use EventSauce\ObjectHydrator\ObjectMapper;
use EventSauce\ObjectHydrator\UnableToHydrateObject;
use EventSauce\ObjectHydrator\UnableToSerializeObject;
use Generator;

class Hydrator implements ObjectMapper
{
    private array $hydrationStack = [];
    public function __construct() {}

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function hydrateObject(string $className, array $payload): object
    {
        return match($className) {
            \WyriHaximus\DevApp\React\SimpleORM\LogStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\CommentStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\UserStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($payload),
            default => throw UnableToHydrateObject::noHydrationDefined($className, $this->hydrationStack),
        };
    }

    
    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\LogStub
    {
        $properties = [];
        $missingFields = [];
        try {
            $value = $payload['id'] ?? null;

            if ($value === null) {
                $missingFields[] = 'id';
                goto after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub;
            }

            $properties['id'] = $value;

            after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub:

            $value = $payload['message'] ?? null;

            if ($value === null) {
                $missingFields[] = 'message';
                goto after_message_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub;
            }

            $properties['message'] = $value;

            after_message_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub:

        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\LogStub::class, $exception, stack: $this->hydrationStack);
        }

        if (count($missingFields) > 0) {
            throw UnableToHydrateObject::dueToMissingFields(\WyriHaximus\DevApp\React\SimpleORM\LogStub::class, $missingFields, stack: $this->hydrationStack);
        }

        try {
            return new \WyriHaximus\DevApp\React\SimpleORM\LogStub(...$properties);
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\LogStub::class, $exception, stack: $this->hydrationStack);
        }
    }


    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub
    {
        $properties = [];
        $missingFields = [];
        try {
            $value = $payload['id'] ?? null;

            if ($value === null) {
                $missingFields[] = 'id';
                goto after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['id'] = $value;

            after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['previous_blog_post'] ?? null;

            if ($value === null) {
                $properties['previousBlogPost'] = null;
                goto after_previousBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $previousBlogPostCaster1;

            $previousBlogPostCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'previous_blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

            $value = $previousBlogPostCaster1->cast($value, $this);

            if ($value === null) {
                                $properties['previousBlogPost'] = null;
                goto after_previousBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'previousBlogPost';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['previousBlogPost'] = $value;

            after_previousBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['next_blog_post'] ?? null;

            if ($value === null) {
                $properties['nextBlogPost'] = null;
                goto after_nextBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $nextBlogPostCaster1;

            $nextBlogPostCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'next_blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

            $value = $nextBlogPostCaster1->cast($value, $this);

            if ($value === null) {
                                $properties['nextBlogPost'] = null;
                goto after_nextBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'nextBlogPost';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['nextBlogPost'] = $value;

            after_nextBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['title'] ?? null;

            if ($value === null) {
                $missingFields[] = 'title';
                goto after_title_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['title'] = $value;

            after_title_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['contents'] ?? null;

            if ($value === null) {
                $missingFields[] = 'contents';
                goto after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['contents'] = $value;

            after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['author'] ?? null;

            if ($value === null) {
                $missingFields[] = 'author';
                goto after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $authorCaster1;

            $authorCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'author_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

            $value = $authorCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'author';
                goto after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'author';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['author'] = $value;

            after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['publisher'] ?? null;

            if ($value === null) {
                $missingFields[] = 'publisher';
                goto after_publisher_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $publisherCaster1;

            $publisherCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'publisher_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

            $value = $publisherCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'publisher';
                goto after_publisher_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'publisher';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['publisher'] = $value;

            after_publisher_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['comments'] ?? null;

            if ($value === null) {
                $missingFields[] = 'comments';
                goto after_comments_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $commentsCaster1;

            $commentsCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\LeftJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'id',
     'foreignKey' => 'blog_post_id',
     'localCast' => 'BIGINT',
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

            $value = $commentsCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'comments';
                goto after_comments_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['comments'] = $value;

            after_comments_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['views'] ?? null;

            if ($value === null) {
                $missingFields[] = 'views';
                goto after_views_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $viewsCaster1;

            $viewsCaster1 ??= new \EventSauce\ObjectHydrator\PropertyCasters\CastToType(... [
  0 => 'integer',
]);

            $value = $viewsCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'views';
                goto after_views_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['views'] = $value;

            after_views_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['created'] ?? null;

            if ($value === null) {
                $missingFields[] = 'created';
                goto after_created_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $createdCaster1;

            $createdCaster1 ??= new \EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable(... [
]);

            $value = $createdCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'created';
                goto after_created_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['created'] = $value;

            after_created_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

            $value = $payload['modified'] ?? null;

            if ($value === null) {
                $missingFields[] = 'modified';
                goto after_modified_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            static $modifiedCaster1;

            $modifiedCaster1 ??= new \EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable(... [
]);

            $value = $modifiedCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'modified';
                goto after_modified_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
            }

            $properties['modified'] = $value;

            after_modified_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:

        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class, $exception, stack: $this->hydrationStack);
        }

        if (count($missingFields) > 0) {
            throw UnableToHydrateObject::dueToMissingFields(\WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class, $missingFields, stack: $this->hydrationStack);
        }

        try {
            return new \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub(...$properties);
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class, $exception, stack: $this->hydrationStack);
        }
    }


    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\CommentStub
    {
        $properties = [];
        $missingFields = [];
        try {
            $value = $payload['id'] ?? null;

            if ($value === null) {
                $missingFields[] = 'id';
                goto after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            $properties['id'] = $value;

            after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:

            $value = $payload['author'] ?? null;

            if ($value === null) {
                $missingFields[] = 'author';
                goto after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            static $authorCaster1;

            $authorCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'author_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

            $value = $authorCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'author';
                goto after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'author';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['author'] = $value;

            after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:

            $value = $payload['blog_post'] ?? null;

            if ($value === null) {
                $missingFields[] = 'blog_post';
                goto after_blogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            static $blogPostCaster1;

            $blogPostCaster1 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

            $value = $blogPostCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'blog_post';
                goto after_blogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            if (is_array($value)) {
                try {
                    $this->hydrationStack[] = 'blogPost';
                    $value = $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($value);
                } finally {
                    array_pop($this->hydrationStack);
                }
            }

            $properties['blogPost'] = $value;

            after_blogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:

            $value = $payload['contents'] ?? null;

            if ($value === null) {
                $missingFields[] = 'contents';
                goto after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub;
            }

            $properties['contents'] = $value;

            after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:

        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\CommentStub::class, $exception, stack: $this->hydrationStack);
        }

        if (count($missingFields) > 0) {
            throw UnableToHydrateObject::dueToMissingFields(\WyriHaximus\DevApp\React\SimpleORM\CommentStub::class, $missingFields, stack: $this->hydrationStack);
        }

        try {
            return new \WyriHaximus\DevApp\React\SimpleORM\CommentStub(...$properties);
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\CommentStub::class, $exception, stack: $this->hydrationStack);
        }
    }


    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\UserStub
    {
        $properties = [];
        $missingFields = [];
        try {
            $value = $payload['id'] ?? null;

            if ($value === null) {
                $missingFields[] = 'id';
                goto after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub;
            }

            $properties['id'] = $value;

            after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub:

            $value = $payload['name'] ?? null;

            if ($value === null) {
                $missingFields[] = 'name';
                goto after_name_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub;
            }

            $properties['name'] = $value;

            after_name_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub:

        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\UserStub::class, $exception, stack: $this->hydrationStack);
        }

        if (count($missingFields) > 0) {
            throw UnableToHydrateObject::dueToMissingFields(\WyriHaximus\DevApp\React\SimpleORM\UserStub::class, $missingFields, stack: $this->hydrationStack);
        }

        try {
            return new \WyriHaximus\DevApp\React\SimpleORM\UserStub(...$properties);
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\UserStub::class, $exception, stack: $this->hydrationStack);
        }
    }

    private function serializeViaTypeMap(string $accessor, object $object, array $payloadToTypeMap): array
    {
        foreach ($payloadToTypeMap as $payloadType => [$valueType, $method]) {
            if ($object instanceof $valueType) {
                return [$accessor => $payloadType] + $this->{$method}($object);
            }
        }

        throw new \LogicException('No type mapped for object of class: ' . $object::class);
    }

    public function serializeObject(object $object): mixed
    {
        return $this->serializeObjectOfType($object, $object::class);
    }

    /**
     * @template T
     *
     * @param T               $object
     * @param class-string<T> $className
     */
    public function serializeObjectOfType(object $object, string $className): mixed
    {
        try {
            return match($className) {
                'array' => $this->serializeValuearray($object),
            \Ramsey\Uuid\UuidInterface::class => $this->serializeValueRamsey⚡️Uuid⚡️UuidInterface($object),
            'DateTime' => $this->serializeValueDateTime($object),
            'DateTimeImmutable' => $this->serializeValueDateTimeImmutable($object),
            'DateTimeInterface' => $this->serializeValueDateTimeInterface($object),
            \WyriHaximus\DevApp\React\SimpleORM\LogStub::class => $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub($object),
            \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class => $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($object),
            \WyriHaximus\DevApp\React\SimpleORM\CommentStub::class => $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub($object),
            \WyriHaximus\DevApp\React\SimpleORM\UserStub::class => $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($object),
                default => throw new \LogicException("No serialization defined for $className"),
            };
        } catch (\Throwable $exception) {
            throw UnableToSerializeObject::dueToError($className, $exception);
        }
    }

    
    private function serializeValuearray(mixed $value): mixed
    {
        static $serializer;

        $serializer ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeArrayItems(... [
]);

        return $serializer->serialize($value, $this);
    }


    private function serializeValueRamsey⚡️Uuid⚡️UuidInterface(mixed $value): mixed
    {
        static $serializer;

        $serializer ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeUuidToString(... [
]);

        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTime(mixed $value): mixed
    {
        static $serializer;

        $serializer ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]);

        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTimeImmutable(mixed $value): mixed
    {
        static $serializer;

        $serializer ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]);

        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTimeInterface(mixed $value): mixed
    {
        static $serializer;

        $serializer ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]);

        return $serializer->serialize($value, $this);
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\LogStub);
        $result = [];

        $id = $object->id;
        after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub:        $result['id'] = $id;

        
        $message = $object->message;
        after_message_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub:        $result['message'] = $message;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub);
        $result = [];

        $id = $object->id;
        after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['id'] = $id;

        
        $previousBlogPost = $object->previousBlogPost;

        if (!$previousBlogPost instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub) {
            goto after_previousBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
        }
        static $previousBlogPostSerializer0;

        $previousBlogPostSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'previous_blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

        $previousBlogPost = $previousBlogPostSerializer0->serialize($previousBlogPost, $this);
        after_previousBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['previous_blog_post'] = $previousBlogPost;

        
        $nextBlogPost = $object->nextBlogPost;

        if (!$nextBlogPost instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub) {
            goto after_nextBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub;
        }
        static $nextBlogPostSerializer0;

        $nextBlogPostSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'next_blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

        $nextBlogPost = $nextBlogPostSerializer0->serialize($nextBlogPost, $this);
        after_nextBlogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['next_blog_post'] = $nextBlogPost;

        
        $title = $object->title;
        after_title_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['title'] = $title;

        
        $contents = $object->contents;
        after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['contents'] = $contents;

        
        $author = $object->author;
        static $authorSerializer0;

        $authorSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'author_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

        $author = $authorSerializer0->serialize($author, $this);
        after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['author'] = $author;

        
        $publisher = $object->publisher;
        static $publisherSerializer0;

        $publisherSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'publisher_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

        $publisher = $publisherSerializer0->serialize($publisher, $this);
        after_publisher_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['publisher'] = $publisher;

        
        $comments = $object->comments;
        static $commentsSerializer0;

        $commentsSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\LeftJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'id',
     'foreignKey' => 'blog_post_id',
     'localCast' => 'BIGINT',
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
  'lazy' => true,
]);

        $comments = $commentsSerializer0->serialize($comments, $this);
        after_comments_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['comments'] = $comments;

        
        $views = $object->views;
        static $viewsSerializer0;

        $viewsSerializer0 ??= new \EventSauce\ObjectHydrator\PropertyCasters\CastToType(... [
  0 => 'integer',
]);

        $views = $viewsSerializer0->serialize($views, $this);
        after_views_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['views'] = $views;

        
        $created = $object->created;
        static $createdSerializer0;

        $createdSerializer0 ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]);

        $created = $createdSerializer0->serialize($created, $this);
        after_created_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['created'] = $created;

        
        $modified = $object->modified;
        static $modifiedSerializer0;

        $modifiedSerializer0 ??= new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]);

        $modified = $modifiedSerializer0->serialize($modified, $this);
        after_modified_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub:        $result['modified'] = $modified;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\CommentStub);
        $result = [];

        $id = $object->id;
        after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:        $result['id'] = $id;

        
        $author = $object->author;
        static $authorSerializer0;

        $authorSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'author_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

        $author = $authorSerializer0->serialize($author, $this);
        after_author_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:        $result['author'] = $author;

        
        $blogPost = $object->blogPost;
        static $blogPostSerializer0;

        $blogPostSerializer0 ??= new \WyriHaximus\React\SimpleORM\Attribute\InnerJoin(... [
  'clause' => 
 [
  0 => 
  \WyriHaximus\React\SimpleORM\Attribute\Clause::__set_state([
     'localKey' => 'blog_post_id',
     'foreignKey' => 'id',
     'localCast' => NULL,
     'localFunction' => NULL,
     'foreignCast' => NULL,
     'foreignFunction' => NULL,
  ]),
],
]);

        $blogPost = $blogPostSerializer0->serialize($blogPost, $this);
        after_blogPost_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:        $result['blog_post'] = $blogPost;

        
        $contents = $object->contents;
        after_contents_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub:        $result['contents'] = $contents;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\UserStub);
        $result = [];

        $id = $object->id;
        after_id_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub:        $result['id'] = $id;

        
        $name = $object->name;
        after_name_WyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub:        $result['name'] = $name;


        return $result;
    }



    /**
     * @template T
     *
     * @param class-string<T> $className
     * @param iterable<array> $payloads;
     *
     * @return IterableList<T>
     *
     * @throws UnableToHydrateObject
     */
    public function hydrateObjects(string $className, iterable $payloads): IterableList
    {
        return new IterableList($this->doHydrateObjects($className, $payloads));
    }

    private function doHydrateObjects(string $className, iterable $payloads): Generator
    {
        foreach ($payloads as $index => $payload) {
            yield $index => $this->hydrateObject($className, $payload);
        }
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     * @param iterable<array> $payloads;
     *
     * @return IterableList<T>
     *
     * @throws UnableToSerializeObject
     */
    public function serializeObjects(iterable $payloads): IterableList
    {
        return new IterableList($this->doSerializeObjects($payloads));
    }

    private function doSerializeObjects(iterable $objects): Generator
    {
        foreach ($objects as $index => $object) {
            yield $index => $this->serializeObject($object);
        }
    }
}
