<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use PhpParser\Node\Expr;
final class ReplaceServiceArgument
{
    /**
     * @readonly
     * @var mixed
     */
    private $oldValue;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $newValueExpr;
    /**
     * @param mixed $oldValue
     */
    public function __construct($oldValue, Expr $newValueExpr)
    {
        $this->oldValue = $oldValue;
        $this->newValueExpr = $newValueExpr;
    }
    /**
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }
    public function getNewValueExpr() : Expr
    {
        return $this->newValueExpr;
    }
}
