<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
use Rector\Core\Validation\RectorAssert;
final class ArrayCallableDynamicMethod
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $callerExpr;
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $method;
    public function __construct(\PhpParser\Node\Expr $callerExpr, string $class, \PhpParser\Node\Expr $method)
    {
        $this->callerExpr = $callerExpr;
        $this->class = $class;
        $this->method = $method;
        \Rector\Core\Validation\RectorAssert::className($class);
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
