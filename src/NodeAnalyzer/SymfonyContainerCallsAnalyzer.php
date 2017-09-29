<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;

final class SymfonyContainerCallsAnalyzer
{
    /**
     * Finds $this->get(...);
     */
    public function isThisCall(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof Variable) {
            return false;
        }

        if (! $methodCall->name instanceof Identifier) {
            return false;
        }

        $variableName = $methodCall->var->name;
        $methodName = (string) $methodCall->name;

        if ($variableName !== 'this' || $methodName !== 'get') {
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
