<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
use Rector\Core\Validation\RectorAssert;
/**
 * @api
 */
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
    public function __construct(Expr $callerExpr, string $class, Expr $method)
    {
        $this->callerExpr = $callerExpr;
        $this->class = $class;
        $this->method = $method;
        RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : Expr
    {
        return $this->method;
    }
    public function getCallerExpr() : Expr
    {
        return $this->callerExpr;
    }
}
