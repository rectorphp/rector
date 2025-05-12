<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFinder\DataProviderClassMethodFinder;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector\StaticDataProviderClassMethodRectorTest
 */
final class StaticDataProviderClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DataProviderClassMethodFinder $dataProviderClassMethodFinder;
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderClassMethodFinder $dataProviderClassMethodFinder, VisibilityManipulator $visibilityManipulator, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderClassMethodFinder = $dataProviderClassMethodFinder;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change data provider methods to static', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
    }

    public function provideData()
    {
        yield [1];
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
    public function test()
    {
    }

    public static function provideData()
    {
        yield [1];
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
        // 1. find all data providers
        $dataProviderClassMethods = $this->dataProviderClassMethodFinder->find($node);
        $hasChanged = \false;
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            if ($this->skipMethod($dataProviderClassMethod)) {
                continue;
            }
            $this->visibilityManipulator->makeStatic($dataProviderClassMethod);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function skipMethod(ClassMethod $classMethod) : bool
    {
        if ($classMethod->isStatic()) {
            return \true;
        }
        if ($classMethod->stmts === null) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($classMethod->stmts, fn(Node $node): bool => $node instanceof Variable && $this->isName($node, 'this'));
    }
}
