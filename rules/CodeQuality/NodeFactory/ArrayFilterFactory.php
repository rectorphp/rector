<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
final class ArrayFilterFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function createSimpleFuncCallAssign(\PhpParser\Node\Stmt\Foreach_ $foreach, string $funcName, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PhpParser\Node\Expr\Assign
    {
        $string = new \PhpParser\Node\Scalar\String_($funcName);
        $args = [new \PhpParser\Node\Arg($foreach->expr), new \PhpParser\Node\Arg($string)];
        $arrayFilterFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_filter'), $args);
        return new \PhpParser\Node\Expr\Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    public function createWithClosure(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \PhpParser\Node\Expr\Variable $valueVariable, \PhpParser\Node\Expr $condExpr, \PhpParser\Node\Stmt\Foreach_ $foreach) : \PhpParser\Node\Expr\Assign
    {
        $filterFunction = $this->createClosure($valueVariable, $condExpr);
        $args = [new \PhpParser\Node\Arg($foreach->expr), new \PhpParser\Node\Arg($filterFunction)];
        $arrayFilterFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_filter'), $args);
        return new \PhpParser\Node\Expr\Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    /**
     * @return \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure
     */
    private function createClosure(\PhpParser\Node\Expr\Variable $valueVariable, \PhpParser\Node\Expr $condExpr)
    {
        $params = [new \PhpParser\Node\Param($valueVariable)];
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::ARROW_FUNCTION)) {
            return new \PhpParser\Node\Expr\ArrowFunction(['params' => $params, 'expr' => $condExpr]);
        }
        return new \PhpParser\Node\Expr\Closure(['params' => $params, 'stmts' => [new \PhpParser\Node\Stmt\Return_($condExpr)]]);
    }
}
