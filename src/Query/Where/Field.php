<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Where;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use WyriHaximus\React\SimpleORM\Query\WhereInterface;

final class Field implements WhereInterface
{
    /** @param mixed[] $criteriaArguments */
    public function __construct(private string $field, private string $criteria, private array $criteriaArguments = [])
    {
    }

    public function field(): string
    {
        return $this->field;
    }

    public function applyCriteria(CriteriaBuilder $criteria): CriteriaInterface
    {
        /** @phpstan-ignore-next-line */
        return $criteria->{$this->criteria}(...$this->criteriaArguments);
    }
}
