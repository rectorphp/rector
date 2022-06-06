<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector\ReplaceTestAnnotationWithPrefixedFunctionRectorTest
 */
final class ReplaceTestAnnotationWithPrefixedFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace @test with prefixed function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($this->isName($node->name, 'test*')) {
            return null;
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof \PhpParser\Comment\Doc) {
            return null;
        }
        if (\strpos($docComment->getText(), '@test') === \false) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'test');
        $node->name->name = 'test' . \ucfirst($node->name->name);
        return $node;
    }
}
