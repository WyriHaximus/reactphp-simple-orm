<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\QueryInterface;
use React\Promise\PromiseInterface;
use Rx\Observable;
use function ApiClients\Tools\Rx\unwrapObservableFromPromise;
use function React\Promise\resolve;

final class Where
{
    private string $field;
    private string $criteria;
    /** @var mixed[]  */
    private array $criteriaArguments = [];

    /**
     * @param mixed[] $criteriaArguments
     */
    public function __construct(string $field, string $criteria, array $criteriaArguments)
    {
        $this->field = $field;
        $this->criteria = $criteria;
        $this->criteriaArguments = $criteriaArguments;
    }

    public function field(): string
    {
        return $this->field;
    }

    public function applyCriteria(CriteriaBuilder $criteria): CriteriaInterface
    {
        return $criteria->{$this->criteria}(...$this->criteriaArguments);
    }
}
