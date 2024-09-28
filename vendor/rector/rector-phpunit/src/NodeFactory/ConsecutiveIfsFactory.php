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
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
final class ConsecutiveIfsFactory
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory
     */
    private $matcherInvocationCountMethodCallNodeFactory;
    public function __construct(\Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory)
    {
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
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
                $assertMethodCall->name = $this->createAssertMethodName($assertMethodCall);
                $assertMethodCall->args[] = new Arg(new ArrayDimFetch($parametersVariable, new LNumber($assertKey)));
                $ifStmts[] = new Expression($assertMethodCall);
            }
            $ifs[] = new If_(new Identical($matcherMethodCall, new LNumber($key + 1)), ['stmts' => $ifStmts]);
        }
        return $ifs;
    }
    private function createAssertMethodName(MethodCall $assertMethodCall) : Identifier
    {
        if (!$assertMethodCall->name instanceof Identifier) {
            throw new ShouldNotHappenException();
        }
        if ($assertMethodCall->name->toString() === 'equalTo') {
            return new Identifier('assertEquals');
        }
        throw new NotImplementedYetException($assertMethodCall->name->toString());
    }
}
