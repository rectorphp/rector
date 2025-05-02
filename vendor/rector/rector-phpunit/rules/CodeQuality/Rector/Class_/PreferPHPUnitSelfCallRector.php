<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\AssertMethodAnalyzer;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector\PreferPHPUnitSelfCallRectorTest
 */
final class PreferPHPUnitSelfCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private AssertMethodAnalyzer $assertMethodAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AssertMethodAnalyzer $assertMethodAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->assertMethodAnalyzer = $assertMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes PHPUnit calls from $this->assert*() to self::assert*()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        $this->assertEquals('expected', $result);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        self::assertEquals('expected', $result);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged) : ?StaticCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->assertMethodAnalyzer->detectTestCaseCallForStatic($node)) {
                return null;
            }
            $methodName = $this->getName($node->name);
            $hasChanged = \true;
            return $this->nodeFactory->createStaticCall('self', $methodName, $node->getArgs());
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
