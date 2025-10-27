<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ReturnIteratorInDataProviderRector\ReturnIteratorInDataProviderRectorTest
 */
final class ReturnIteratorInDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DataProviderMethodsFinder $dataProviderMethodsFinder;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add Iterator type on known PHPUnit data providers', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testSomething($value)
    {
    }

    public function provideData()
    {
        yield [5];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function testSomething($value)
    {
    }

    public function provideData(): \Iterator
    {
        yield [5];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $dataProviderClassMethods = $this->dataProviderMethodsFinder->findDataProviderNodesInClass($node);
        $hasChanged = \false;
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            if ($dataProviderClassMethod->returnType instanceof Node) {
                continue;
            }
            if (!$this->hasYields($dataProviderClassMethod)) {
                continue;
            }
            $dataProviderClassMethod->returnType = new FullyQualified('Iterator');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function hasYields(ClassMethod $classMethod): bool
    {
        $yields = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, Yield_::class);
        return $yields !== [];
    }
}
