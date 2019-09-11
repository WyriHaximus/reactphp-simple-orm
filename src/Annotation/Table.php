<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Table
{
    /** @var string */
    private $table;

    /**
     * @param string[] $table
     */
    public function __construct(array $table)
    {
        $this->table = \current($table);
    }

    public function getTable(): string
    {
        return $this->table;
    }
}
