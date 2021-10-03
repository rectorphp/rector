<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
final class ArrayCallableDynamicMethod
{
    /**
     * @var \PhpParser\Node\Expr
     */
    private $callerExpr;
    /**
     * @var string
     */
    private $class;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $method;
    public function __construct(\PhpParser\Node\Expr $callerExpr, string $class, \PhpParser\Node\Expr $method)
    {
        $this->callerExpr = $callerExpr;
        $this->class = $class;
        $this->method = $method;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : \PhpParser\Node\Expr
    {
        return $this->method;
    }
    public function getCallerExpr() : \PhpParser\Node\Expr
    {
        return $this->callerExpr;
    }
}
