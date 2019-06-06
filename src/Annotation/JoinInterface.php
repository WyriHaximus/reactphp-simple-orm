<?php declare(strict_types=1);

namespace WyriHaximus\React\SimpleORM\Annotation;

interface JoinInterface
{
    public function getEntity(): string;

    public function getType(): string;

    public function getLocalKey(): string;

    public function getForeignKey(): string;

    public function getProperty(): string;

    public function getLocalCast(): ?string;

    public function getForeignCast(): ?string;
}
