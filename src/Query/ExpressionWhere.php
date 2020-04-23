<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use Latitude\QueryBuilder\Partial\Criteria;
use Latitude\QueryBuilder\QueryFactory;
use Latitude\QueryBuilder\QueryInterface;
use React\Promise\PromiseInterface;
use Rx\Observable;
use function ApiClients\Tools\Rx\unwrapObservableFromPromise;
use function React\Promise\resolve;

final class ExpressionWhere
{
    private ExpressionInterface $expression;
    private string $criteria;
    /** @var mixed[]  */
    private array $criteriaArguments = [];

    /**
     * @param mixed[] $criteriaArguments
     */
    public function __construct(ExpressionInterface $expression, string $criteria, array $criteriaArguments)
    {
        $this->expression = $expression;
        $this->criteria = $criteria;
        $this->criteriaArguments = $criteriaArguments;
    }

    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function applyExpression(ExpressionInterface $expression): CriteriaInterface
    {
        return (new CriteriaBuilder($expression))->{$this->criteria}(...$this->criteriaArguments);
    }
}
