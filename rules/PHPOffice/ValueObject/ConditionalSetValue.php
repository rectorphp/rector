<?php

declare(strict_types=1);

namespace Rector\PHPOffice\ValueObject;

final class ConditionalSetValue
{
    public function __construct(
        private string $oldMethod,
        private string $newGetMethod,
        private string $newSetMethod,
        private int $argPosition,
        private bool $hasRow
    ) {
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getArgPosition(): int
    {
        return $this->argPosition;
    }

    public function getNewGetMethod(): string
    {
        return $this->newGetMethod;
    }

    public function getNewSetMethod(): string
    {
        return $this->newSetMethod;
    }

    public function hasRow(): bool
    {
        return $this->hasRow;
    }
}
