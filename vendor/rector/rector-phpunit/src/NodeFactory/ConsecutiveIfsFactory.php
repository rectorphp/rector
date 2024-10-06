<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\CodeQuality\NodeFactory\NestedClosureAssertFactory;
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
    /**
     * @readonly
     * @var \Rector\PHPUnit\CodeQuality\NodeFactory\NestedClosureAssertFactory
     */
    private $nestedClosureAssertFactory;
    public function __construct(\Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory, NodeNameResolver $nodeNameResolver, NestedClosureAssertFactory $nestedClosureAssertFactory)
    {
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nestedClosureAssertFactory = $nestedClosureAssertFactory;
    }
    /**
     * @return If_[]
     */
    public function createCombinedIfs(MethodCall $withConsecutiveMethodCall, MethodCall $willReturnOnConsecutiveCallsArgument) : array
    {
        $ifs = [];
        $matcherMethodCall = $this->matcherInvocationCountMethodCallNodeFactory->create();
        $willReturnArgs = $willReturnOnConsecutiveCallsArgument->getArgs();
        foreach ($withConsecutiveMethodCall->getArgs() as $key => $withConsecutiveArg) {
            if (!$withConsecutiveArg->value instanceof Array_) {
                throw new ShouldNotHappenException();
            }
            $ifStmts = [];
            $args = [new Arg($withConsecutiveArg->value), new Arg(new Variable('parameters'))];
            $ifStmts[] = new Expression(new MethodCall(new Variable('this'), 'assertSame', $args));
            $willReturnArg = $willReturnArgs[$key] ?? null;
            if ($willReturnArg instanceof Arg) {
                $ifStmts[] = new Return_($willReturnArg->value);
            }
            $ifs[] = new If_(new Identical($matcherMethodCall, new LNumber($key + 1)), ['stmts' => $ifStmts]);
        }
        return $ifs;
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
                    $args = [new Arg($assertArrayItem), new Arg(new Variable('parameters'))];
                    $ifStmts[] = new Expression(new MethodCall(new Variable('this'), 'assertSame', $args));
                    continue;
                }
                $assertMethodCall = $assertArrayItem->value;
                if ($this->nodeNameResolver->isName($assertMethodCall->name, 'equalTo')) {
                    $ifStmts[] = $this->createAssertMethodCall($assertMethodCall, $parametersVariable, $assertKey);
                } elseif ($this->nodeNameResolver->isName($assertMethodCall->name, 'callback')) {
                    $ifStmts = \array_merge($ifStmts, $this->nestedClosureAssertFactory->create($assertMethodCall, $assertKey));
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
}
