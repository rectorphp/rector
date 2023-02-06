<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
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
        return [Class_::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $coversClassAttributeGroups = $this->resolveCoversClassAttributeGroups($node);
        $coversFunctionsAttributeGroups = $this->resolveCoversFunctionAttributeGroups($node);
        $attributeGroups = \array_merge($coversClassAttributeGroups, $coversFunctionsAttributeGroups);
        if ($attributeGroups === []) {
            return null;
        }
        $node->attrGroups = \array_merge($node->attrGroups, $attributeGroups);
        return $node;
    }
    private function createAttributeGroup(string $annotationValue) : AttributeGroup
    {
        if (\strncmp($annotationValue, '::', \strlen('::')) === 0) {
            $attributeClass = 'PHPUnit\\Framework\\Attributes\\CoversFunction';
            $attributeValue = \trim($annotationValue, ':()');
        } else {
            $attributeClass = 'PHPUnit\\Framework\\Attributes\\CoversClass';
            $attributeValue = \trim($annotationValue) . '::class';
        }
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, [$attributeValue]);
    }
    /**
     * @return AttributeGroup[]
     */
    private function resolveCoversClassAttributeGroups(Class_ $class) : array
    {
        // resolve covers class first
        $classPhpDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        if (!$classPhpDocInfo instanceof PhpDocInfo) {
            return [];
        }
        $attributeGroups = [];
        /** @var PhpDocTagNode[] $desiredTagValueNodes */
        $desiredTagValueNodes = $classPhpDocInfo->getTagsByName('covers');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $attributeGroups[] = $this->createAttributeGroup($desiredTagValueNode->value->value);
            // cleanup
            $this->phpDocTagRemover->removeTagValueFromNode($classPhpDocInfo, $desiredTagValueNode);
        }
        return $attributeGroups;
    }
    /**
     * @return AttributeGroup[]
     */
    private function resolveCoversFunctionAttributeGroups(Class_ $class) : array
    {
        $attributeGroups = [];
        // resolve covers function
        foreach ($class->getMethods() as $classMethod) {
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
            if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            /** @var PhpDocTagNode[] $desiredTagValueNodes */
            $desiredTagValueNodes = $classMethodPhpDocInfo->getTagsByName('covers');
            foreach ($desiredTagValueNodes as $desiredTagValueNode) {
                if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                $attributeGroups[] = $this->createAttributeGroup($desiredTagValueNode->value->value);
                // cleanup
                $this->phpDocTagRemover->removeTagValueFromNode($classMethodPhpDocInfo, $desiredTagValueNode);
            }
        }
        return $attributeGroups;
    }
}
