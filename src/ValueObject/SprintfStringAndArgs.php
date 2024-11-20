<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
final class SprintfStringAndArgs
{
    /**
     * @readonly
     */
    private String_ $string;
    /**
     * @var Expr[]
     * @readonly
     */
    private array $arrayItems;
    /**
     * @param Expr[] $arrayItems
     */
    public function __construct(String_ $string, array $arrayItems)
    {
        $this->string = $string;
        $this->arrayItems = $arrayItems;
    }
    /**
     * @return Expr[]
     */
    public function getArrayItems() : array
    {
        return $this->arrayItems;
    }
    public function getStringValue() : string
    {
        return $this->string->value;
    }
}
