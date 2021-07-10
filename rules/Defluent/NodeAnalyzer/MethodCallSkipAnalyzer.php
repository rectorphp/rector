<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Defluent\Skipper\FluentMethodCallSkipper;

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
}
