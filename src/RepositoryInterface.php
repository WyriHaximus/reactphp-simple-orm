<?php

declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use WyriHaximus\React\SimpleORM\Query\Order;
use WyriHaximus\React\SimpleORM\Query\SectionInterface;
use WyriHaximus\React\SimpleORM\Query\Where;

/**
 * @api
 * @template T of EntityInterface
 */
interface RepositoryInterface
{
    public const int DEFAULT_PER_PAGE = 50;

    /** @phpstan-ignore ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration */
    public function count(Where|null $where = null): int;

    /**
     * @return iterable<T>
     *
     * @phpstan-ignore ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullDefaultValue,ergebnis.noParameterWithNullableTypeDeclaration,ergebnis.noParameterWithNullableTypeDeclaration
     */
    public function page(int $page, Where|null $where = null, Order|null $order = null, int $perPage = self::DEFAULT_PER_PAGE): iterable;

    /** @return iterable<T> */
    public function fetch(SectionInterface ...$sections): iterable;

    /** @return T */
    public function first(SectionInterface ...$sections): EntityInterface;

    /** @return iterable<T> */
    public function stream(SectionInterface ...$sections): iterable;

    /**
     * @param array<string, mixed> $fields
     *
     * @return T
     */
    public function create(array $fields): EntityInterface;

    /** @return T */
    public function update(EntityInterface $entity): EntityInterface;

    public function delete(EntityInterface $entity): null;
}
