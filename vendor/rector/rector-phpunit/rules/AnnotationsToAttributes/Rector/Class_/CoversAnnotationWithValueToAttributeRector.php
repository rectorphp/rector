<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector\CoversAnnotationWithValueToAttributeRectorTest
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
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
     * @covers ::someFunction()
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
#[CoversFunction('someFunction')]
final class SomeTest extends TestCase
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
        if ($node instanceof Class_) {
            $coversAttributeGroups = $this->resolveClassAttributes($node);
            if ($coversAttributeGroups === []) {
                return null;
            }
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            $node->attrGroups = \array_merge($node->attrGroups, $coversAttributeGroups);
            return $node;
        }
        $hasChanged = $this->removeMethodCoversAnnotations($node);
        if ($hasChanged === \false) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
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
     * @return array<string, AttributeGroup>
     */
    private function resolveClassAttributes(Class_ $class) : array
    {
        $coversDefaultGroups = [];
        $coversGroups = [];
        $methodGroups = [];
        $hasCoversDefault = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $coversDefaultGroups = $this->handleCoversDefaultClass($phpDocInfo);
            // If there is a ::coversDefaultClass, @covers ::function will refer to class methods, otherwise it will refer to global functions.
            $hasCoversDefault = $coversDefaultGroups !== [];
            $coversGroups = $this->handleCovers($phpDocInfo, $hasCoversDefault);
        }
        foreach ($class->getMethods() as $classMethod) {
            $methodGroups = \array_merge($methodGroups, $this->resolveMethodAttributes($classMethod, $hasCoversDefault));
        }
        return \array_merge($coversDefaultGroups, $coversGroups, $methodGroups);
    }
    /**
     * @return AttributeGroup[]
     */
    private function handleCoversDefaultClass(PhpDocInfo $phpDocInfo) : array
    {
        $attributeGroups = [];
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('coversDefaultClass');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $attributeGroups[] = $this->createAttributeGroup($desiredTagValueNode->value->value);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        return $attributeGroups;
    }
    /**
     * @return array<string, AttributeGroup>
     */
    private function handleCovers(PhpDocInfo $phpDocInfo, bool $hasCoversDefault) : array
    {
        $attributeGroups = [];
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('covers');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $covers = $desiredTagValueNode->value->value;
            if (\strncmp($covers, '\\', \strlen('\\')) === 0) {
                $attributeGroups[$covers] = $this->createAttributeGroup($covers);
            } elseif (!$hasCoversDefault && \strncmp($covers, '::', \strlen('::')) === 0) {
                $attributeGroups[$covers] = $this->createAttributeGroup($covers);
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        return $attributeGroups;
    }
    /**
     * @return array<string, AttributeGroup>
     */
    private function resolveMethodAttributes(ClassMethod $classMethod, bool $hasCoversDefault) : array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return [];
        }
        $attributeGroups = [];
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('covers');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $covers = $desiredTagValueNode->value->value;
            if (\strncmp($covers, '\\', \strlen('\\')) === 0) {
                $covers = $this->getClass($covers);
                $attributeGroups[$covers] = $this->createAttributeGroup($covers);
            } elseif (!$hasCoversDefault && \strncmp($covers, '::', \strlen('::')) === 0) {
                $attributeGroups[$covers] = $this->createAttributeGroup($covers);
            }
        }
        return $attributeGroups;
    }
    private function removeMethodCoversAnnotations(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $hasChanged = \false;
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('covers');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    private function getClass(string $classWithMethod) : string
    {
        return Strings::replace($classWithMethod, '/::.*$/');
    }
}
