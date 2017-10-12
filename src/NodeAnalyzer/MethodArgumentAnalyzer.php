<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;

final class MethodArgumentAnalyzer
{
    public function hasMethodFirstArgument(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! isset($node->args[0]) || ! $node->args[0] instanceof Arg) {
            return false;
        }

        return true;
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

        if (! $node->args[0]->value instanceof String_) {
            return false;
        }

        return true;
    }
}
