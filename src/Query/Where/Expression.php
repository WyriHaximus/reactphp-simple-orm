<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Where;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use Latitude\QueryBuilder\ExpressionInterface;
use WyriHaximus\React\SimpleORM\Query\WhereInterface;

final class Expression implements WhereInterface
{
    /** @param mixed[] $criteriaArguments */
    public function __construct(private ExpressionInterface $expression, private string $criteria, private array $criteriaArguments = [])
    {
    }

    public function expression(): ExpressionInterface
    {
        return $this->expression;
    }

    public function applyExpression(ExpressionInterface $expression): CriteriaInterface
    {
        /** @phpstan-ignore-next-line */
        return (new CriteriaBuilder($expression))->{$this->criteria}(...$this->criteriaArguments);
    }
}
