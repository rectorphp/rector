<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
final class NestedClosureAssertFactory
{
    /**
     * @return Stmt[]
     */
    public function create(MethodCall $assertMethodCall, int $assertKey) : array
    {
        $callableFirstArg = $assertMethodCall->getArgs()[0];
        if ($callableFirstArg->value instanceof ArrowFunction) {
            $arrowFunction = $callableFirstArg->value;
            if ($arrowFunction->expr instanceof Identical) {
                // unwrap closure arrow function to direct assert as more readalbe
                $identical = $arrowFunction->expr;
                if ($identical->left instanceof Variable) {
                    return $this->createAssertSameParameters($identical->right, $assertKey);
                }
                if ($identical->right instanceof Variable) {
                    return $this->createAssertSameParameters($identical->left, $assertKey);
                }
            }
        }
        $callbackVariable = new Variable('callback');
        $callbackAssign = new Assign($callbackVariable, $callableFirstArg->value);
        $stmts = [];
        $stmts[] = new Expression($callbackAssign);
        $callbackVariable = new Variable('callback');
        $parametersArrayDimFetch = new ArrayDimFetch(new Variable('parameters'), new LNumber($assertKey));
        $callbackFuncCall = new FuncCall($callbackVariable, [new Arg($parametersArrayDimFetch)]);
        // add assert true to the callback
        $assertTrueMethodCall = new MethodCall(new Variable('this'), 'assertTrue', [new Arg($callbackFuncCall)]);
        $stmts[] = new Expression($assertTrueMethodCall);
        return $stmts;
    }
    /**
     * @return Stmt[]
     */
    private function createAssertSameParameters(Expr $comparedExpr, int $assertKey) : array
    {
        // use assert same directly instead
        $args = [new Arg($comparedExpr), new Arg(new ArrayDimFetch(new Variable('parameters'), new LNumber($assertKey)))];
        $assertSameMethodCall = new MethodCall(new Variable('this'), new Identifier('assertSame'), $args);
        return [new Expression($assertSameMethodCall)];
    }
}
