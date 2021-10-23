<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
final class ExpectExceptionMessageRegExpFactory
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
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PHPUnit\NodeFactory\ArgumentShiftingFactory $argumentShiftingFactory, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->argumentShiftingFactory = $argumentShiftingFactory;
        $this->nodeComparator = $nodeComparator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function create(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\Variable $exceptionVariable) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($methodCall, 'assertContains')) {
            return null;
        }
        $secondArgument = $methodCall->args[1]->value;
        if (!$secondArgument instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        // looking for "$exception->getMessage()"
        if (!$this->nodeComparator->areNodesEqual($secondArgument->var, $exceptionVariable)) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($secondArgument->name, 'getMessage')) {
            return null;
        }
        $this->argumentShiftingFactory->removeAllButFirstArgMethodCall($methodCall, 'expectExceptionMessageRegExp');
        // put regex between "#...#" to create match
        if ($methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            /** @var String_ $oldString */
            $oldString = $methodCall->args[0]->value;
            $methodCall->args[0]->value = new \PhpParser\Node\Scalar\String_('#' . \preg_quote($oldString->value, '#') . '#');
        }
        return $methodCall;
    }
}
