<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\ValueObject;

final class UnionTypeAnalysis
{
    public function __construct(
        private bool $isNullableType,
        private bool $hasIterable,
        private bool $hasArray
    ) {
    }

    public function isNullableType(): bool
    {
        return $this->isNullableType;
    }

    public function hasIterable(): bool
    {
        return $this->hasIterable;
    }

    public function hasArray(): bool
    {
        return $this->hasArray;
    }
}
