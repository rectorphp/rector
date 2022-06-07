<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
use Rector\Core\Validation\RectorAssert;
final class ArrayCallable
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
     * @var string
     */
    private $method;
    public function __construct(Expr $callerExpr, string $class, string $method)
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
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getCallerExpr() : Expr
    {
        return $this->callerExpr;
    }
}
