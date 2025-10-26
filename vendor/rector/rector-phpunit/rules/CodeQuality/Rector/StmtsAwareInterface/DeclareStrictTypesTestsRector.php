<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Rector\ValueObject\Application\File;
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
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        parent::beforeTraverse($nodes);
        if ($this->shouldSkipNodes($nodes, $this->file)) {
            return null;
        }
        /** @var Node $rootStmt */
        $rootStmt = current($nodes);
        // when first stmt is Declare_, verify if there is strict_types definition already,
        // as multiple declare is allowed, with declare(strict_types=1) only allowed on very first stmt
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($rootStmt)) {
            return null;
        }
        if (!$this->hasPHPUnitTestClass($nodes)) {
            return null;
        }
        $rectorWithLineChange = new RectorWithLineChange(self::class, $rootStmt->getStartLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        return array_merge([$this->nodeFactory->createDeclaresStrictType(), new Nop()], $nodes);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): int
    {
        // workaround, as Rector now only hooks to specific nodes, not arrays
        // avoid traversing, as we already handled in beforeTraverse()
        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param Stmt[] $nodes
     */
    private function shouldSkipNodes(array $nodes, File $file): bool
    {
        if ($this->skipper->shouldSkipElementAndFilePath(self::class, $file->getFilePath())) {
            return \true;
        }
        if (strncmp($file->getFileContent(), '#!', strlen('#!')) === 0) {
            return \true;
        }
        return $nodes === [];
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
