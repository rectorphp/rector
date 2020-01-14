<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\ValueObject;

final class UnionTypeAnalysis
{
    /**
     * @var bool
     */
    private $isNullableType = false;

    /**
     * @var bool
     */
    private $hasIterable = false;

    public function __construct(bool $isNullableType, bool $hasIterable)
    {
        $this->isNullableType = $isNullableType;
        $this->hasIterable = $hasIterable;
    }

    public function isNullableType(): bool
    {
        return $this->isNullableType;
    }

    public function hasIterable(): bool
    {
        return $this->hasIterable;
    }
}
