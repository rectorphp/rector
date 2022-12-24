<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\CoversAnnotationWithValueToAttributeRector\CoversAnnotationWithValueToAttributeRectorTest
 */
final class CoversAnnotationWithValueToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change covers annotations with value to attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

/**
 * @covers SomeClass
 */
final class SomeTest extends TestCase
{
    /**
     * @covers ::someFunction
     */
    public function test()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversClass(SomeClass::class)]
final class SomeTest extends TestCase
{
    #[CoversFunction('someFunction')]
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
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $hasChanged = \false;
        /** @var PhpDocTagNode[] $desiredTagValueNodes */
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('covers');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $annotationValue = $desiredTagValueNode->value->value;
            if (\strncmp($annotationValue, '::', \strlen('::')) === 0) {
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\CoversFunction';
                $attributeValue = \trim($annotationValue, ':()');
            } else {
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\CoversClass';
                $attributeValue = \trim($annotationValue) . '::class';
            }
            $hasChanged = \true;
            $attributeGroup = $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, [$attributeValue]);
            $node->attrGroups[] = $attributeGroup;
            // cleanup
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
