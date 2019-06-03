<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PgAsync\Client as PgClient;
use Plasma\SQL\Grammar\PostgreSQL;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;

final class Client implements ClientInterface
{
    /** @var PgClient */
    private $client;

    /** @var EntityInspector */
    private $entityInspector;

    /** @var Repository[] */
    private $repositories = [];

    public function __construct(PgClient $client, ?Reader $annotationReader = null)
    {
        $this->client = $client;
        $this->entityInspector = new EntityInspector($annotationReader ?? new AnnotationReader());
    }

    public function getRepository(string $entity): Repository
    {
        if (!isset($this->repositories[$entity])) {
            $this->repositories[$entity] = new Repository($this->entityInspector->getEntity($entity), $this);
        }

        return $this->repositories[$entity];
    }

    public function fetch(QueryBuilder $query): Observable
    {
        $query = $query->withGrammar(new PostgreSQL());

        return $this->client->executeStatement($query->getQuery(), $query->getParameters());
    }
}
