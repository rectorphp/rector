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

    /**
     * @var bool
     */
    private $hasArray = false;

    public function __construct(bool $isNullableType, bool $hasIterable, bool $hasArray)
    {
        $this->isNullableType = $isNullableType;
        $this->hasIterable = $hasIterable;
        $this->hasArray = $hasArray;
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
