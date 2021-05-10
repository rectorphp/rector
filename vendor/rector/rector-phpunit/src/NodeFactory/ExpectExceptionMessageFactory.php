<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
final class ExpectExceptionMessageFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var NodeComparator
     */
    private $nodeComparator;
    /**
     * @var ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, \Rector\PHPUnit\NodeFactory\ArgumentShiftingFactory $argumentShiftingFactory, NodeComparator $nodeComparator, NodeTypeResolver $nodeTypeResolver, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->nodeComparator = $nodeComparator;
        $this->nodeTypeResolver = $nodeTypeResolver;
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
        if (!$this->nodeComparator->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($secondArgument->name, 'getMessage')) {
            return null;
        }
        $this->argumentShiftingFactory->removeAllButFirstArgMethodCall($methodCall, 'expectExceptionMessage');
        return $methodCall;
    }
}
