<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use ReflectionClass;
use ReflectionProperty;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class InnerJoin
{
    /** @var string */
    private $entity;

    /** @var string */
    private $local_key;

    /** @var string */
    private $local_cast;

    /** @var string */
    private $foreign_key;

    /** @var string */
    private $foreign_cast;

    /** @var string */
    private $property;

    public function __construct(array $table)
    {
        /** @var ReflectionProperty $property */
        foreach ((new ReflectionClass(self::class))->getProperties() as $property) {
            $propertyName = $property->getName();
            if (isset($table[$propertyName])) {
                $this->$propertyName = $table[$propertyName];
            }
        }
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getLocalKey(): string
    {
        return $this->local_key;
    }

    public function getForeignKey(): string
    {
        return $this->foreign_key;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getLocalCast(): ?string
    {
        return $this->local_cast;
    }

    public function getForeignCast(): ?string
    {
        return $this->foreign_cast;
    }
}
