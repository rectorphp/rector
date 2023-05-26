<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Symfony\Printer\NeighbourClassLikePrinter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassLike\RemoveTestSuffixFromAbstractTestClassesRector\RemoveTestSuffixFromAbstractTestClassesRectorTest
 */
final class RemoveTestSuffixFromAbstractTestClassesRector extends AbstractRector
{
    /**
     * @var \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_|null
     */
    private $rootNode = null;
    /**
     * @readonly
     * @var \Rector\Symfony\Printer\NeighbourClassLikePrinter
     */
    private $neighbourClassLikePrinter;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(NeighbourClassLikePrinter $neighbourClassLikePrinter, TestsNodeAnalyzer $testsNodeAnalyzer, RenamedClassesDataCollector $renamedClassesDataCollector, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->neighbourClassLikePrinter = $neighbourClassLikePrinter;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    /**
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        // ensure reset early on every run to avoid reuse existing value
        $this->rootNode = null;
        foreach ($nodes as $node) {
            if ($node instanceof FileWithoutNamespace || $node instanceof Namespace_) {
                $this->rootNode = $node;
                break;
            }
        }
        return parent::beforeTraverse($nodes);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename abstract test class suffix from "*Test" to "*TestCase"', [new CodeSample(<<<'CODE_SAMPLE'
// tests/AbstractTest.php
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// tests/AbstractTestCase.php
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
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
        if (!$node->isAbstract()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (!$this->isName($node->name, '*Test')) {
            return null;
        }
        /** @var string $oldClassName */
        $oldClassName = $this->getName($node);
        // rename class
        $testCaseClassName = $node->name->toString() . 'Case';
        $node->name = new Identifier($testCaseClassName);
        $this->printNewNodes($node);
        // to rename all other references
        $this->renamedClassesDataCollector->addOldToNewClass($oldClassName, $oldClassName . 'Case');
        // remove source files that were renamed above
        $this->removedAndAddedFilesCollector->removeFile($this->file->getFilePath());
        return $node;
    }
    private function printNewNodes(Class_ $class) : void
    {
        $filePath = $this->file->getFilePath();
        if (!$this->rootNode instanceof FileWithoutNamespace && !$this->rootNode instanceof Namespace_) {
            throw new ShouldNotHappenException();
        }
        $this->neighbourClassLikePrinter->printClassLike($class, $this->rootNode, $filePath, $this->file);
    }
}
