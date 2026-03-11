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
            \WyriHaximus\DevApp\React\SimpleORM\NoSQLStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️NoSQLStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\LogStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\CommentStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub($payload),
                \WyriHaximus\DevApp\React\SimpleORM\UserStub::class => $this->hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($payload),
            default => throw UnableToHydrateObject::noHydrationDefined($className, $this->hydrationStack),
        };
    }
    
            
    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️NoSQLStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\NoSQLStub
    {
        $properties = []; 
        $missingFields = [];
        try {
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\NoSQLStub::class, $exception, stack: $this->hydrationStack);
        }

        if (count($missingFields) > 0) {
            throw UnableToHydrateObject::dueToMissingFields(\WyriHaximus\DevApp\React\SimpleORM\NoSQLStub::class, $missingFields, stack: $this->hydrationStack);
        }

        try {
            return new \WyriHaximus\DevApp\React\SimpleORM\NoSQLStub(...$properties);
        } catch (\Throwable $exception) {
            throw UnableToHydrateObject::dueToError(\WyriHaximus\DevApp\React\SimpleORM\NoSQLStub::class, $exception, stack: $this->hydrationStack);
        }
    }

        
    private function hydrateWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub(array $payload): \WyriHaximus\DevApp\React\SimpleORM\LogStub
    {
        $properties = []; 
        $missingFields = [];
        try {
            $value = $payload['id'] ?? null;

            if ($value === null) {
                $missingFields[] = 'id';
                goto after_id;
            }

            $properties['id'] = $value;

            after_id:

            $value = $payload['message'] ?? null;

            if ($value === null) {
                $missingFields[] = 'message';
                goto after_message;
            }

            $properties['message'] = $value;

            after_message:

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
                goto after_id;
            }

            $properties['id'] = $value;

            after_id:

            $value = $payload['previous_blog_post'] ?? null;

            if ($value === null) {
                $properties['previousBlogPost'] = null;
                goto after_previousBlogPost;
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

            after_previousBlogPost:

            $value = $payload['next_blog_post'] ?? null;

            if ($value === null) {
                $properties['nextBlogPost'] = null;
                goto after_nextBlogPost;
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

            after_nextBlogPost:

            $value = $payload['title'] ?? null;

            if ($value === null) {
                $missingFields[] = 'title';
                goto after_title;
            }

            $properties['title'] = $value;

            after_title:

            $value = $payload['contents'] ?? null;

            if ($value === null) {
                $missingFields[] = 'contents';
                goto after_contents;
            }

            $properties['contents'] = $value;

            after_contents:

            $value = $payload['author'] ?? null;

            if ($value === null) {
                $missingFields[] = 'author';
                goto after_author;
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

            after_author:

            $value = $payload['publisher'] ?? null;

            if ($value === null) {
                $missingFields[] = 'publisher';
                goto after_publisher;
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

            after_publisher:

            $value = $payload['comments'] ?? null;

            if ($value === null) {
                $missingFields[] = 'comments';
                goto after_comments;
            }

            $properties['comments'] = $value;

            after_comments:

            $value = $payload['views'] ?? null;

            if ($value === null) {
                $missingFields[] = 'views';
                goto after_views;
            }

            static $viewsCaster1;

            if ($viewsCaster1 === null) {
                $viewsCaster1 = new \EventSauce\ObjectHydrator\PropertyCasters\CastToType(... [
  0 => 'integer',
]
);
            }

            $value = $viewsCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'views';
                goto after_views;
            }

            $properties['views'] = $value;

            after_views:

            $value = $payload['created'] ?? null;

            if ($value === null) {
                $missingFields[] = 'created';
                goto after_created;
            }

            static $createdCaster1;

            if ($createdCaster1 === null) {
                $createdCaster1 = new \EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable(... [
]
);
            }

            $value = $createdCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'created';
                goto after_created;
            }

            $properties['created'] = $value;

            after_created:

            $value = $payload['modified'] ?? null;

            if ($value === null) {
                $missingFields[] = 'modified';
                goto after_modified;
            }

            static $modifiedCaster1;

            if ($modifiedCaster1 === null) {
                $modifiedCaster1 = new \EventSauce\ObjectHydrator\PropertyCasters\CastToDateTimeImmutable(... [
]
);
            }

            $value = $modifiedCaster1->cast($value, $this);

            if ($value === null) {
                                $missingFields[] = 'modified';
                goto after_modified;
            }

            $properties['modified'] = $value;

            after_modified:

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
                goto after_id;
            }

            $properties['id'] = $value;

            after_id:

            $value = $payload['author'] ?? null;

            if ($value === null) {
                $missingFields[] = 'author';
                goto after_author;
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

            after_author:

            $value = $payload['blog_post'] ?? null;

            if ($value === null) {
                $missingFields[] = 'blog_post';
                goto after_blogPost;
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

            after_blogPost:

            $value = $payload['contents'] ?? null;

            if ($value === null) {
                $missingFields[] = 'contents';
                goto after_contents;
            }

            $properties['contents'] = $value;

            after_contents:

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
                goto after_id;
            }

            $properties['id'] = $value;

            after_id:

            $value = $payload['name'] ?? null;

            if ($value === null) {
                $missingFields[] = 'name';
                goto after_name;
            }

            $properties['name'] = $value;

            after_name:

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
            \WyriHaximus\DevApp\React\SimpleORM\NoSQLStub::class => $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️NoSQLStub($object),
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
        
        if ($serializer === null) {
            $serializer = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeArrayItems(... [
]
);
        }
        
        return $serializer->serialize($value, $this);
    }


    private function serializeValueRamsey⚡️Uuid⚡️UuidInterface(mixed $value): mixed
    {
        static $serializer;
        
        if ($serializer === null) {
            $serializer = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeUuidToString(... [
]
);
        }
        
        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTime(mixed $value): mixed
    {
        static $serializer;
        
        if ($serializer === null) {
            $serializer = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]
);
        }
        
        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTimeImmutable(mixed $value): mixed
    {
        static $serializer;
        
        if ($serializer === null) {
            $serializer = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]
);
        }
        
        return $serializer->serialize($value, $this);
    }


    private function serializeValueDateTimeInterface(mixed $value): mixed
    {
        static $serializer;
        
        if ($serializer === null) {
            $serializer = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]
);
        }
        
        return $serializer->serialize($value, $this);
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️NoSQLStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\NoSQLStub);
        $result = [];

        $id = $object->id;
        after_id:        $result['id'] = $id;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️LogStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\LogStub);
        $result = [];

        $id = $object->id;
        after_id:        $result['id'] = $id;

        
        $message = $object->message;
        after_message:        $result['message'] = $message;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub);
        $result = [];

        $id = $object->id;
        after_id:        $result['id'] = $id;

        
        $previousBlogPost = $object->previousBlogPost;

        if (!$previousBlogPost instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub) {
            goto after_previousBlogPost;
        }
        $previousBlogPost = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($previousBlogPost);
        after_previousBlogPost:        $result['previous_blog_post'] = $previousBlogPost;

        
        $nextBlogPost = $object->nextBlogPost;

        if (!$nextBlogPost instanceof \WyriHaximus\DevApp\React\SimpleORM\BlogPostStub) {
            goto after_nextBlogPost;
        }
        $nextBlogPost = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($nextBlogPost);
        after_nextBlogPost:        $result['next_blog_post'] = $nextBlogPost;

        
        $title = $object->title;
        after_title:        $result['title'] = $title;

        
        $contents = $object->contents;
        after_contents:        $result['contents'] = $contents;

        
        $author = $object->author;
        $author = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($author);
        after_author:        $result['author'] = $author;

        
        $publisher = $object->publisher;
        $publisher = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($publisher);
        after_publisher:        $result['publisher'] = $publisher;

        
        $comments = $object->comments;
        after_comments:        $result['comments'] = $comments;

        
        $views = $object->views;
        static $viewsSerializer0;

        if ($viewsSerializer0 === null) {
            $viewsSerializer0 = new \EventSauce\ObjectHydrator\PropertyCasters\CastToType(... [
  0 => 'integer',
]
);
        }
        
        $views = $viewsSerializer0->serialize($views, $this);
        after_views:        $result['views'] = $views;

        
        $created = $object->created;
        static $createdSerializer0;

        if ($createdSerializer0 === null) {
            $createdSerializer0 = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]
);
        }
        
        $created = $createdSerializer0->serialize($created, $this);
        after_created:        $result['created'] = $created;

        
        $modified = $object->modified;
        static $modifiedSerializer0;

        if ($modifiedSerializer0 === null) {
            $modifiedSerializer0 = new \EventSauce\ObjectHydrator\PropertySerializers\SerializeDateTime(... [
]
);
        }
        
        $modified = $modifiedSerializer0->serialize($modified, $this);
        after_modified:        $result['modified'] = $modified;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️CommentStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\CommentStub);
        $result = [];

        $id = $object->id;
        after_id:        $result['id'] = $id;

        
        $author = $object->author;
        $author = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub($author);
        after_author:        $result['author'] = $author;

        
        $blogPost = $object->blogPost;
        $blogPost = $this->serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️BlogPostStub($blogPost);
        after_blogPost:        $result['blog_post'] = $blogPost;

        
        $contents = $object->contents;
        after_contents:        $result['contents'] = $contents;


        return $result;
    }


    private function serializeObjectWyriHaximus⚡️DevApp⚡️React⚡️SimpleORM⚡️UserStub(mixed $object): mixed
    {
        \assert($object instanceof \WyriHaximus\DevApp\React\SimpleORM\UserStub);
        $result = [];

        $id = $object->id;
        after_id:        $result['id'] = $id;

        
        $name = $object->name;
        after_name:        $result['name'] = $name;


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
