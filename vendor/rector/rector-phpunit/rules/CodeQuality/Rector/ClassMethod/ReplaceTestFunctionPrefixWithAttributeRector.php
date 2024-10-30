<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector\ReplaceTestAnnotationWithPrefixedFunctionRectorTest
 */
final class ReplaceTestFunctionPrefixWithAttributeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace @test with prefixed function', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function onePlusOneShouldBeTwo()
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
        if (\strncmp($node->name->toString(), 'test', \strlen('test')) !== 0) {
            return null;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttributes($node, ['PHPUnit\\Framework\\Attributes\\Test'])) {
            return null;
        }
        if ($node->name->toString() !== 'test' && $node->name->toString() !== 'test_') {
            if (\strncmp($node->name->toString(), 'test_', \strlen('test_')) === 0) {
                $node->name->name = \lcfirst(\substr($node->name->name, 5));
            } elseif (\strncmp($node->name->toString(), 'test', \strlen('test')) === 0) {
                $node->name->name = \lcfirst(\substr($node->name->name, 4));
            }
        }
        $coversAttributeGroup = $this->createAttributeGroup();
        $node->attrGroups = \array_merge($node->attrGroups, [$coversAttributeGroup]);
        return $node;
    }
    private function createAttributeGroup() : AttributeGroup
    {
        $attributeClass = 'PHPUnit\\Framework\\Attributes\\Test';
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, []);
    }
}
