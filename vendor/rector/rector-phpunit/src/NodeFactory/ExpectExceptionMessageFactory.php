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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\PHPUnit\NodeFactory\ArgumentShiftingFactory
     */
    private $argumentShiftingFactory;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PHPUnit\NodeFactory\ArgumentShiftingFactory $argumentShiftingFactory, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->nodeComparator = $nodeComparator;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function create(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Variable $exceptionVariable) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($methodCall, ['assertSame', 'assertEquals'])) {
            return null;
        }
        $secondArgument = $methodCall->args[1]->value;
        if (!$secondArgument instanceof \PhpParser\Node\Expr\MethodCall) {
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
