<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\FileNode;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector\DeclareStrictTypesTestsRectorTest
 */
final class DeclareStrictTypesTestsRector extends AbstractRector implements HTMLAverseRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private DeclareStrictTypeFinder $declareStrictTypeFinder;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder, TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add `declare(strict_types=1)` to PHPUnit test class file', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTestWithoutStrict extends TestCase
{
    public function test()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SomeTestWithoutStrict extends TestCase
{
    public function test()
    {
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
        return [FileNode::class];
    }
    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?FileNode
    {
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($node)) {
            return null;
        }
        if (!$this->hasPHPUnitTestClass($node->stmts)) {
            return null;
        }
        $declareStrictTypes = $this->nodeFactory->createDeclaresStrictType();
        $node->stmts = array_merge([$declareStrictTypes, new Nop()], $node->stmts);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param Stmt[] $nodes
     */
    private function hasPHPUnitTestClass(array $nodes): bool
    {
        $class = $this->betterNodeFinder->findFirstNonAnonymousClass($nodes);
        if (!$class instanceof Class_) {
            return \false;
        }
        return $this->testsNodeAnalyzer->isInTestClass($class);
    }
}
