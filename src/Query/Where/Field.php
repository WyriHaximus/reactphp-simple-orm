<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Where;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use WyriHaximus\React\SimpleORM\Query\WhereInterface;

final class Field implements WhereInterface
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
        $this->field             = $field;
        $this->criteria          = $criteria;
        $this->criteriaArguments = $criteriaArguments;
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
