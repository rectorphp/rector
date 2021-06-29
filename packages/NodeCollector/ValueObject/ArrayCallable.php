<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
final class ArrayCallable
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
     * @var string
     */
    private $method;
    public function __construct(\PhpParser\Node\Expr $callerExpr, string $class, string $method)
    {
        $this->callerExpr = $callerExpr;
        $this->class = $class;
        $this->method = $method;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getCallerExpr() : \PhpParser\Node\Expr
    {
        return $this->callerExpr;
    }
}
