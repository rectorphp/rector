<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PHPUnit\Enum\AssertMethod;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertIssetToSpecificMethodRector\AssertIssetToSpecificMethodRectorTest
 */
final class AssertIssetToSpecificMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private IdentifierManipulator $identifierManipulator;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns assertTrue() + isset() comparisons to more precise assertArrayHasKey() method', [new CodeSample('$this->assertTrue(isset($anything["foo"]), "message");', '$this->assertArrayHasKey("foo", $anything, "message");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, [AssertMethod::ASSERT_TRUE, AssertMethod::ASSERT_FALSE])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $firstArgumentValue = $firstArg->value;
        // is property access
        if (!$firstArgumentValue instanceof Isset_) {
            return null;
        }
        $issetExpr = $firstArgumentValue->vars[0];
        if (!$issetExpr instanceof ArrayDimFetch) {
            return null;
        }
        return $this->refactorArrayDimFetchNode($node, $issetExpr);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function refactorArrayDimFetchNode($node, ArrayDimFetch $arrayDimFetch) : Node
    {
        $this->identifierManipulator->renameNodeWithMap($node, [AssertMethod::ASSERT_TRUE => 'assertArrayHasKey', AssertMethod::ASSERT_FALSE => 'assertArrayNotHasKey']);
        $oldArgs = $node->getArgs();
        unset($oldArgs[0]);
        $node->args = \array_merge($this->nodeFactory->createArgs([$arrayDimFetch->dim, $arrayDimFetch->var]), $oldArgs);
        return $node;
    }
}
