<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
final class ExpectExceptionCodeFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;
    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, \Rector\PHPUnit\NodeFactory\ArgumentShiftingFactory $argumentShiftingFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function create(MethodCall $methodCall, Variable $exceptionVariable) : ?MethodCall
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($methodCall, ['assertSame', 'assertEquals'])) {
            return null;
        }
        $secondArgument = $methodCall->args[1]->value;
        if (!$secondArgument instanceof MethodCall) {
            return null;
        }
        // looking for "$exception->getMessage()"
        if (!$this->nodeNameResolver->areNamesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($secondArgument->name, 'getCode')) {
            return null;
        }
        $this->argumentShiftingFactory->removeAllButFirstArgMethodCall($methodCall, 'expectExceptionCode');
        return $methodCall;
    }
}
