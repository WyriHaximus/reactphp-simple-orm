<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use WyriHaximus\React\SimpleORM\Annotation\Table;

final class Repository
{
    /** @var Client */
    private $client;

    /** @var Hydrator */
    private $hydrator;

    /** @var string */
    private $table;

    public function __construct(Client $client, string $entity)
    {
        $this->client = $client;
        $this->hydrator = new Hydrator();
        $this->table = (new AnnotationReader())->getClassAnnotation(new \ReflectionClass($entity), Table::class)->getTable();
    }
}
