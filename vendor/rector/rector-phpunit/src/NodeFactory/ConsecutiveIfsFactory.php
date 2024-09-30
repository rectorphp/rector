<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
final class ConsecutiveIfsFactory
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory
     */
    private $matcherInvocationCountMethodCallNodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return If_[]
     */
    public function createIfs(MethodCall $withConsecutiveMethodCall) : array
    {
        $ifs = [];
        $matcherMethodCall = $this->matcherInvocationCountMethodCallNodeFactory->create();
        $parametersVariable = new Variable(ConsecutiveVariable::PARAMETERS);
        foreach ($withConsecutiveMethodCall->getArgs() as $key => $withConsecutiveArg) {
            if (!$withConsecutiveArg->value instanceof Array_) {
                throw new ShouldNotHappenException();
            }
            $ifStmts = [];
            foreach ($withConsecutiveArg->value->items as $assertKey => $assertArrayItem) {
                if (!$assertArrayItem instanceof ArrayItem) {
                    continue;
                }
                if (!$assertArrayItem->value instanceof MethodCall) {
                    throw new NotImplementedYetException();
                }
                $assertMethodCall = $assertArrayItem->value;
                if ($this->nodeNameResolver->isName($assertMethodCall->name, 'equalTo')) {
                    $assertMethodCallExpression = $this->createAssertMethodCall($assertMethodCall, $parametersVariable, $assertKey);
                    $ifStmts[] = $assertMethodCallExpression;
                } elseif ($this->nodeNameResolver->isName($assertMethodCall->name, 'callback')) {
                    $ifStmts[] = $this->createClosureAssignExpression($assertMethodCall);
                    $ifStmts[] = $this->createAssertClosureExpression($parametersVariable, $assertKey);
                } else {
                    $methodName = $this->nodeNameResolver->getName($assertMethodCall->name);
                    throw new NotImplementedYetException($methodName ?? 'dynamic name');
                }
            }
            $ifs[] = new If_(new Identical($matcherMethodCall, new LNumber($key + 1)), ['stmts' => $ifStmts]);
        }
        return $ifs;
    }
    private function createAssertMethodCall(MethodCall $assertMethodCall, Variable $parametersVariable, int $parameterPositionKey) : Expression
    {
        $assertMethodCall->name = new Identifier('assertEquals');
        $parametersArrayDimFetch = new ArrayDimFetch($parametersVariable, new LNumber($parameterPositionKey));
        $assertMethodCall->args[] = new Arg($parametersArrayDimFetch);
        return new Expression($assertMethodCall);
    }
    private function createClosureAssignExpression(MethodCall $assertMethodCall) : Expression
    {
        $callableFirstArg = $assertMethodCall->getArgs()[0];
        $callbackVariable = new Variable('callback');
        $callbackAssign = new Assign($callbackVariable, $callableFirstArg->value);
        return new Expression($callbackAssign);
    }
    private function createAssertClosureExpression(Variable $parametersVariable, int $parameterPositionKey) : Expression
    {
        $callbackVariable = new Variable('callback');
        $parametersArrayDimFetch = new ArrayDimFetch($parametersVariable, new LNumber($parameterPositionKey));
        $callbackFuncCall = new FuncCall($callbackVariable, [new Arg($parametersArrayDimFetch)]);
        // add assert true to the callback
        $assertTrueMethodCall = new MethodCall(new Variable('this'), 'assertTrue', [new Arg($callbackFuncCall)]);
        return new Expression($assertTrueMethodCall);
    }
}
