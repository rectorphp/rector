<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
final class ExpectExceptionFactory
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function create(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($methodCall, 'assertInstanceOf')) {
            return null;
        }
        $argumentVariableName = $this->nodeNameResolver->getName($methodCall->args[1]->value);
        if ($argumentVariableName === null) {
            return null;
        }
        // is na exception variable
        if (!$this->nodeNameResolver->isName($variable, $argumentVariableName)) {
            return null;
        }
        return new \PhpParser\Node\Expr\MethodCall($methodCall->var, 'expectException', [$methodCall->args[0]]);
    }
}
