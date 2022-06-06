<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\ValueObject;

final class UnionTypeAnalysis
{
    /**
     * @readonly
     * @var bool
     */
    private $isNullableType;
    /**
     * @readonly
     * @var bool
     */
    private $hasIterable;
    /**
     * @readonly
     * @var bool
     */
    private $hasArray;
    public function __construct(bool $isNullableType, bool $hasIterable, bool $hasArray)
    {
        $this->isNullableType = $isNullableType;
        $this->hasIterable = $hasIterable;
        $this->hasArray = $hasArray;
    }
    public function isNullableType() : bool
    {
        return $this->isNullableType;
    }
    public function hasIterable() : bool
    {
        return $this->hasIterable;
    }
    public function hasArray() : bool
    {
        return $this->hasArray;
    }
}
