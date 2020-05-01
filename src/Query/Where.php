<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Query;

use Latitude\QueryBuilder\Builder\CriteriaBuilder;
use Latitude\QueryBuilder\CriteriaInterface;

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
        return $criteria->{$this->criteria}(...$this->criteriaArguments);
    }
}
