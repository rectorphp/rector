<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
final class ExpectationMock
{
    /**
     * @var Variable|PropertyFetch
     */
    private $expectationVariable;
    /**
     * @var Arg[]
     */
    private $methodArguments;
    /**
     * @var int
     */
    private $index;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $expr;
    /**
     * @var array<int, (null | Expr)>
     */
    private $withArguments;
    /**
     * @var \PhpParser\Node\Stmt\Expression|null
     */
    private $originalExpression;
    /**
     * @param Variable|PropertyFetch $expectationVariable
     * @param Arg[] $methodArguments
     * @param array<int, null|Expr> $withArguments
     */
    public function __construct(\PhpParser\Node\Expr $expectationVariable, array $methodArguments, int $index, ?\PhpParser\Node\Expr $expr, array $withArguments, ?\PhpParser\Node\Stmt\Expression $originalExpression)
    {
        $this->expectationVariable = $expectationVariable;
        $this->methodArguments = $methodArguments;
        $this->index = $index;
        $this->expr = $expr;
        $this->withArguments = $withArguments;
        $this->originalExpression = $originalExpression;
    }
    /**
     * @return Variable|PropertyFetch
     */
    public function getExpectationVariable() : \PhpParser\Node\Expr
    {
        return $this->expectationVariable;
    }
    /**
     * @return Arg[]
     */
    public function getMethodArguments() : array
    {
        return $this->methodArguments;
    }
    public function getIndex() : int
    {
        return $this->index;
    }
    public function getReturn() : ?\PhpParser\Node\Expr
    {
        return $this->expr;
    }
    /**
     * @return array<int, null|Expr>
     */
    public function getWithArguments() : array
    {
        return $this->withArguments;
    }
    public function getOriginalExpression() : ?\PhpParser\Node\Stmt\Expression
    {
        return $this->originalExpression;
    }
}
