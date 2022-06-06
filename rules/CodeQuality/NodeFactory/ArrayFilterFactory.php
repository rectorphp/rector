<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrowFunction;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Php\PhpVersionProvider;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
final class ArrayFilterFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function createSimpleFuncCallAssign(Foreach_ $foreach, string $funcName, ArrayDimFetch $arrayDimFetch) : Assign
    {
        $string = new String_($funcName);
        $args = [new Arg($foreach->expr), new Arg($string)];
        $arrayFilterFuncCall = new FuncCall(new Name('array_filter'), $args);
        return new Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    public function createWithClosure(ArrayDimFetch $arrayDimFetch, Variable $valueVariable, Expr $condExpr, Foreach_ $foreach) : Assign
    {
        $filterFunction = $this->createClosure($valueVariable, $condExpr);
        $args = [new Arg($foreach->expr), new Arg($filterFunction)];
        $arrayFilterFuncCall = new FuncCall(new Name('array_filter'), $args);
        return new Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    /**
     * @return \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure
     */
    private function createClosure(Variable $valueVariable, Expr $condExpr)
    {
        $params = [new Param($valueVariable)];
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ARROW_FUNCTION)) {
            return new ArrowFunction(['params' => $params, 'expr' => $condExpr]);
        }
        return new Closure(['params' => $params, 'stmts' => [new Return_($condExpr)]]);
    }
}
