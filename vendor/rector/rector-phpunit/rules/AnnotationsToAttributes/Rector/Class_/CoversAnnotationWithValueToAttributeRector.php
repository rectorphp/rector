<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\Reflection\ReflectionProvider;
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
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const COVERS_FUNCTION_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\CoversFunction';
    /**
     * @var string
     */
    private const COVERTS_CLASS_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\CoversClass';
    /**
     * @var string
     */
    private const COVERTS_TRAIT_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\CoversTrait';
    /**
     * @var string
     */
    private const COVERS_METHOD_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\CoversMethod';
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
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
        if (!$this->reflectionProvider->hasClass(self::COVERS_FUNCTION_ATTRIBUTE)) {
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
    private function createAttributeGroup(string $annotationValue) : ?AttributeGroup
    {
        if (\strncmp($annotationValue, '::', \strlen('::')) === 0) {
            $attributeClass = self::COVERS_FUNCTION_ATTRIBUTE;
            $attributeValue = [\trim($annotationValue, ':()')];
        } elseif (\strpos($annotationValue, '::') !== \false) {
            $attributeClass = self::COVERS_METHOD_ATTRIBUTE;
            if (!$this->reflectionProvider->hasClass($attributeClass)) {
                return null;
            }
            $attributeValue = [$this->getClass($annotationValue) . '::class', $this->getMethod($annotationValue)];
        } else {
            $attributeClass = self::COVERTS_CLASS_ATTRIBUTE;
            if ($this->reflectionProvider->hasClass($annotationValue)) {
                $classReflection = $this->reflectionProvider->getClass($annotationValue);
                if ($classReflection->isTrait()) {
                    $attributeClass = self::COVERTS_TRAIT_ATTRIBUTE;
                    if (!$this->reflectionProvider->hasClass($attributeClass)) {
                        return null;
                    }
                }
            }
            $attributeValue = [\trim($annotationValue) . '::class'];
        }
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, $attributeValue);
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
            $attributeGroup = $this->createAttributeGroup($desiredTagValueNode->value->value);
            // phpunit 10 may not fully support attribute
            if (!$attributeGroup instanceof AttributeGroup) {
                continue;
            }
            $attributeGroups[] = $attributeGroup;
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
            if (\strncmp($covers, '\\', \strlen('\\')) === 0 || !$hasCoversDefault && \strncmp($covers, '::', \strlen('::')) === 0) {
                $attributeGroup = $this->createAttributeGroup($covers);
                // phpunit 10 may not fully support attribute
                if (!$attributeGroup instanceof AttributeGroup) {
                    continue;
                }
                $attributeGroups[$covers] = $attributeGroup;
                $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
            } elseif ($hasCoversDefault && \strncmp($covers, '::', \strlen('::')) === 0) {
                $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
            }
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
            if (\strncmp($covers, '\\', \strlen('\\')) === 0 || !$hasCoversDefault && \strncmp($covers, '::', \strlen('::')) === 0) {
                $attributeGroup = $this->createAttributeGroup($covers);
                // phpunit 10 may not fully support attribute
                if (!$attributeGroup instanceof AttributeGroup) {
                    continue;
                }
                $attributeGroups[$covers] = $attributeGroup;
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
            if (\strpos($desiredTagValueNode->value->value, '::') !== \false && !$this->reflectionProvider->hasClass(self::COVERS_METHOD_ATTRIBUTE)) {
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
    private function getMethod(string $classWithMethod) : string
    {
        return Strings::replace($classWithMethod, '/^.*::/');
    }
}
