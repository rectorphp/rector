<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

final class SymfonyContainerCallsAnalyzer
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    /**
     * Finds $this->get(...);
     */
    public function isThisCall(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof Variable) {
            return false;
        }

        if ($methodCall->name instanceof Variable) {
            $methodName = $methodCall->name->name;
        } else {
            $methodName = (string) $methodCall->name;
        }

        if ($methodCall->var->name !== 'this' || $methodName !== 'get') {
            return false;
        }

        return $this->hasOneStringArgument($methodCall);
    }

    /**
     * Finds $this->getContainer()->get(...);
     */
    public function isGetContainerCall(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof MethodCall) {
            return false;
        }

        if ((string) $methodCall->var->var->name !== 'this' || (string) $methodCall->name !== 'get') {
            return false;
        }

        return $this->hasOneStringArgument($methodCall);
    }

    /**
     * Finds ('some_service')
     */
    private function hasOneStringArgument(MethodCall $methodCall): bool
    {
        return count($methodCall->args) === 1 && $methodCall->args[0]->value instanceof String_;
    }
}
