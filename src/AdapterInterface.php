<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM;

use Plasma\SQL\GrammarInterface;
use Plasma\SQL\QueryBuilder;
use Rx\Observable;

interface AdapterInterface
{
    public function query(QueryBuilder $query): Observable;

    public function getGrammar(): GrammarInterface;
}
