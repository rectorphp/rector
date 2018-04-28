<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;

final class MethodArgumentAnalyzer
{
    public function hasMethodFirstArgument(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return isset($node->args[0]) || $node->args[0] instanceof Arg;
    }

    public function hasMethodSecondArgument(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (count($node->args) < 2) {
            return false;
        }

        return true;
    }

    public function isMethodFirstArgumentString(Node $node): bool
    {
        if (! $this->hasMethodFirstArgument($node)) {
            return false;
        }

        return $node->args[0]->value instanceof String_;
    }

    public function isMethodSecondArgumentNull(MethodCall $methodCallNode): bool
    {
        if (! $this->hasMethodSecondArgument($methodCallNode)) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $value = $methodCallNode->args[1]->value;
        if (! $value instanceof ConstFetch) {
            return false;
        }

        /** @var Identifier $nodeName */
        $nodeName = $value->name;

        return $nodeName->toLowerString() === 'null';
    }
}
