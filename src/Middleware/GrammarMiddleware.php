<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Middleware;

use Plasma\SQL\GrammarInterface;
use Plasma\SQL\QueryBuilder;
use React\Promise\PromiseInterface;
use WyriHaximus\React\SimpleORM\MiddlewareInterface;
use function React\Promise\resolve;

final class GrammarMiddleware implements MiddlewareInterface
{
    /** @var GrammarInterface */
    private $grammer;

    public function __construct(GrammarInterface $grammer)
    {
        $this->grammer = $grammer;
    }

    public function query(QueryBuilder $query): PromiseInterface
    {
        return resolve($query->withGrammar($this->grammer));
    }
}
