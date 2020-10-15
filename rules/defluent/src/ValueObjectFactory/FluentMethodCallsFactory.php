<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObjectFactory;

use PhpParser\Node\Expr\MethodCall;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\ValueObject\FluentMethodCalls;

final class FluentMethodCallsFactory
{
    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;

    /**
     * @var SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;

    public function __construct(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer
    ) {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
    }

    public function createFromLastMethodCall(MethodCall $lastMethodCall): ?FluentMethodCalls
    {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($lastMethodCall);
        if (! $this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
            return null;
        }

        // we need at least 2 method call for fluent
        if (count($chainMethodCalls) < 2) {
            return null;
        }

        $rootMethodCall = $this->resolveRootMethodCall($chainMethodCalls);
        return new FluentMethodCalls($rootMethodCall, $chainMethodCalls, $lastMethodCall);
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function resolveRootMethodCall(array $chainMethodCalls): MethodCall
    {
        $lastKey = array_key_last($chainMethodCalls);
        return $chainMethodCalls[$lastKey];
    }
}
