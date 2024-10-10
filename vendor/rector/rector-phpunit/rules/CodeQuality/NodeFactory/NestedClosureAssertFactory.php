<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
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
            if ($arrowFunction->expr instanceof BooleanNot && $arrowFunction->expr->expr instanceof Empty_) {
                return $this->createAssertNotEmpty($assertKey, 'assertNotEmpty');
            }
            if ($arrowFunction->expr instanceof Empty_) {
                return $this->createAssertNotEmpty($assertKey, 'assertEmpty');
            }
        }
        $callbackVariable = new Variable('callback');
        $callbackAssign = new Assign($callbackVariable, $callableFirstArg->value);
        $stmts = [new Expression($callbackAssign)];
        $parametersArrayDimFetch = new ArrayDimFetch(new Variable('parameters'), new LNumber($assertKey));
        $callbackFuncCall = new FuncCall($callbackVariable, [new Arg($parametersArrayDimFetch)]);
        // add assert true to the callback
        $assertTrueMethodCall = new MethodCall(new Variable('this'), 'assertTrue', [new Arg($callbackFuncCall)]);
        $stmts[] = new Expression($assertTrueMethodCall);
        return $stmts;
    }
    /**
     * @return Expression[]
     */
    private function createAssertSameParameters(Expr $comparedExpr, int $assertKey) : array
    {
        // use assert same directly instead
        $args = [new Arg($comparedExpr), new Arg(new ArrayDimFetch(new Variable('parameters'), new LNumber($assertKey)))];
        $assertSameMethodCall = new MethodCall(new Variable('this'), new Identifier('assertSame'), $args);
        return [new Expression($assertSameMethodCall)];
    }
    /**
     * @return Expression[]
     */
    private function createAssertNotEmpty(int $assertKey, string $emptyMethodName) : array
    {
        $arrayDimFetch = new ArrayDimFetch(new Variable(ConsecutiveVariable::PARAMETERS), new LNumber($assertKey));
        $assertEmptyMethodCall = new MethodCall(new Variable('this'), new Identifier($emptyMethodName), [new Arg($arrayDimFetch)]);
        return [new Expression($assertEmptyMethodCall)];
    }
}
