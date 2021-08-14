<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MethodCallSkipAnalyzer
{
    public function __construct(
        private FluentMethodCallSkipper $fluentMethodCallSkipper,
        private FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer
    ) {
    }

    public function shouldSkipMethodCallIncludingNew(MethodCall $methodCall): bool
    {
        if ($this->fluentMethodCallSkipper->shouldSkipRootMethodCall($methodCall)) {
            return true;
        }

        $chainRootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($methodCall);
        return $chainRootExpr instanceof New_;
    }

    public function shouldSkipLastCallNotReturnThis(MethodCall $methodCall): bool
    {
        return ! $this->fluentChainMethodCallNodeAnalyzer->isMethodCallReturnThis($methodCall);
    }

    public function shouldSkipDependsWithOtherExpr(MethodCall|Cast $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return false;
        }

        if ($parentNode instanceof Return_) {
            return false;
        }

        if ($parentNode instanceof Assign) {
            return ! $parentNode->getAttribute(AttributeKey::PARENT_NODE) instanceof Expression;
        }

        if ($parentNode instanceof Cast) {
            return $this->shouldSkipDependsWithOtherExpr($parentNode);
        }

        return ! $parentNode instanceof Expression;
    }
}
