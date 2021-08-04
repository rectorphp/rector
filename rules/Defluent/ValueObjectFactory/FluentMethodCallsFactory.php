<?php

declare (strict_types=1);
namespace Rector\Defluent\ValueObjectFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\ValueObject\FluentMethodCalls;
final class FluentMethodCallsFactory
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
    }
    public function createFromLastMethodCall(\PhpParser\Node\Expr\MethodCall $lastMethodCall) : ?\Rector\Defluent\ValueObject\FluentMethodCalls
    {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($lastMethodCall);
        if (!$this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
            return null;
        }
        // we need at least 2 method call for fluent
        if (\count($chainMethodCalls) < 2) {
            return null;
        }
        $rootMethodCall = $this->resolveRootMethodCall($chainMethodCalls);
        if (!$rootMethodCall->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        return new \Rector\Defluent\ValueObject\FluentMethodCalls($rootMethodCall, $chainMethodCalls, $lastMethodCall);
    }
    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function resolveRootMethodCall(array $chainMethodCalls) : \PhpParser\Node\Expr\MethodCall
    {
        \end($chainMethodCalls);
        $lastKey = \key($chainMethodCalls);
        return $chainMethodCalls[$lastKey];
    }
}
