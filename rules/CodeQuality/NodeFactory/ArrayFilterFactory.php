<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
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
    /**
     * @param Variable[] $uses
     */
    public function createWithClosure(ArrayDimFetch $arrayDimFetch, Variable $valueVariable, Expr $condExpr, Foreach_ $foreach, array $uses) : Assign
    {
        $filterFunction = $this->createClosure($valueVariable, $condExpr, $uses);
        $args = [new Arg($foreach->expr), new Arg($filterFunction)];
        $arrayFilterFuncCall = new FuncCall(new Name('array_filter'), $args);
        return new Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    /**
     * @param Variable[] $uses
     * @return \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure
     */
    private function createClosure(Variable $valueVariable, Expr $condExpr, array $uses)
    {
        $params = [new Param($valueVariable)];
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ARROW_FUNCTION)) {
            return new ArrowFunction(['params' => $params, 'expr' => $condExpr]);
        }
        return new Closure(['params' => $params, 'stmts' => [new Return_($condExpr)], 'uses' => \array_map(static function (Variable $variable) : ClosureUse {
            return new ClosureUse($variable);
        }, $uses)]);
    }
}
