<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Tools;

use function in_array;

trait WithFieldsTrait
{
    /**
     * @param array<string, mixed> $fields
     */
    public function withFields(array $fields): self
    {
        $clone = clone $this;

        foreach ($fields as $key => $value) {
            if (in_array($key, ['id', 'created', 'modified'], true)) {
                continue;
            }

            $clone->$key = $value;
        }

        return $clone;
    }
}
