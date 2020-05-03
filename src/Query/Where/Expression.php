<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Where;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use WyriHaximus\React\SimpleORM\Query\WhereInterface;

final class Expression implements WhereInterface
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
        $this->expression        = $expression;
        $this->criteria          = $criteria;
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
