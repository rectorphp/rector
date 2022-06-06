<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\ValueObject;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
final class SprintfStringAndArgs
{
    /**
     * @readonly
     * @var \PhpParser\Node\Scalar\String_
     */
    private $string;
    /**
     * @var Expr[]
     * @readonly
     */
    private $arrayItems;
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
