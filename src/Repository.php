<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\Annotation\Table;

final class Repository
{
    /** @var ClientInterface */
    private $client;

    /** @var Hydrator */
    private $hydrator;

    /** @var string */
    private $table;

    public function __construct(ClientInterface $client, string $entity)
    {
        $this->client = $client;
        $this->hydrator = new Hydrator();
        $this->table = (new AnnotationReader())->getClassAnnotation(new \ReflectionClass($entity), Table::class)->getTable();
    }

    public function count(): PromiseInterface
    {
        return $this->client->fetch(
            QueryBuilder::create()->from($this->table)->select([
                'COUNT(*) AS count',
            ])
        )->take(1)->toPromise()->then(function (array $row) {
            return $row['count'];
        });
    }
}
