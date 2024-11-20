<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector\ReplaceTestAnnotationWithPrefixedFunctionRectorTest
 */
final class ReplaceTestAnnotationWithPrefixedFunctionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace @test with prefixed function', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function onePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (\strncmp($node->name->toString(), 'test', \strlen('test')) === 0) {
            return null;
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return null;
        }
        if (\strpos($docComment->getText(), '@test') === \false) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'test');
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->name->name = 'test' . \ucfirst($node->name->name);
        return $node;
    }
}
