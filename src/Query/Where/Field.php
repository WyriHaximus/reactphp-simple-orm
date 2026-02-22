<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query\Where;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;
use WyriHaximus\React\SimpleORM\Query\WhereInterface;

final readonly class Field implements WhereInterface
{
    /**
     * @param mixed[] $criteriaArguments
     *
     * @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue
     */
    public function __construct(
        private string $field,
        private string $criteria,
        private array $criteriaArguments = [],
    ) {
    }

    public function field(): string
    {
        return $this->field;
    }

    public function applyCriteria(CriteriaBuilder $criteria): CriteriaInterface
    {
        /** @phpstan-ignore method.dynamicName,return.type */
        return $criteria->{$this->criteria}(...$this->criteriaArguments);
    }
}
