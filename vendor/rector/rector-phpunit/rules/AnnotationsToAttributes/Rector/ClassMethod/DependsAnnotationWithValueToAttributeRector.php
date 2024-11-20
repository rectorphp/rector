<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
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
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector\DependsAnnotationWithValueToAttributeRectorTest
 */
final class DependsAnnotationWithValueToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
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
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const DEPENDS_ATTRIBUTE = 'PHPUnit\\Framework\\Attributes\\Depends';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change depends annotations with value to attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testOne() {}

    /**
     * @depends testOne
     */
    public function testThree(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testOne() {}

    #[\PHPUnit\Framework\Attributes\Depends('testOne')]
    public function testThree(): void
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
        if (!$this->reflectionProvider->hasClass(self::DEPENDS_ATTRIBUTE)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
            if (!$phpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            /** @var PhpDocTagNode[] $desiredTagValueNodes */
            $desiredTagValueNodes = $phpDocInfo->getTagsByName('depends');
            $currentMethodName = $this->getName($classMethod);
            foreach ($desiredTagValueNodes as $desiredTagValueNode) {
                $attributeNameAndValue = $this->resolveAttributeNameAndValue($desiredTagValueNode, $node, $currentMethodName);
                if ($attributeNameAndValue === []) {
                    continue;
                }
                $attributeGroup = $this->phpAttributeGroupFactory->createFromClassWithItems($attributeNameAndValue[0], [$attributeNameAndValue[1]]);
                $classMethod->attrGroups[] = $attributeGroup;
                // cleanup
                $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return string[]
     */
    private function resolveAttributeNameAndValue(PhpDocTagNode $phpDocTagNode, Class_ $class, string $currentMethodName) : array
    {
        if (!$phpDocTagNode->value instanceof GenericTagValueNode) {
            return [];
        }
        $originalAttributeValue = $phpDocTagNode->value->value;
        $attributeNameAndValue = $this->resolveAttributeValueAndAttributeName($class, $currentMethodName, $originalAttributeValue);
        if ($attributeNameAndValue === null) {
            return [];
        }
        return $attributeNameAndValue;
    }
    /**
     * @return string[]|null
     */
    private function resolveAttributeValueAndAttributeName(Class_ $currentClass, string $currentMethodName, string $originalAttributeValue) : ?array
    {
        // process depends other ClassMethod
        $attributeValue = $this->resolveDependsClassMethod($currentClass, $currentMethodName, $originalAttributeValue);
        $attributeName = self::DEPENDS_ATTRIBUTE;
        if (!\is_string($attributeValue)) {
            // other: depends other Class_
            $attributeValue = $this->resolveDependsClass($originalAttributeValue);
            $attributeName = 'PHPUnit\\Framework\\Attributes\\DependsOnClass';
        }
        if (!\is_string($attributeValue)) {
            // other: depends clone ClassMethod
            $attributeValue = $this->resolveDependsCloneClassMethod($currentClass, $currentMethodName, $originalAttributeValue);
            $attributeName = 'PHPUnit\\Framework\\Attributes\\DependsUsingDeepClone';
        }
        if (!\is_string($attributeValue)) {
            return null;
        }
        return [$attributeName, $attributeValue];
    }
    private function resolveDependsClass(string $attributeValue) : ?string
    {
        if (\substr_compare($attributeValue, '::class', -\strlen('::class')) !== 0) {
            return null;
        }
        $className = \substr($attributeValue, 0, -7);
        return $className . '::class';
    }
    private function resolveDependsClassMethod(Class_ $currentClass, string $currentMethodName, string $attributeValue) : ?string
    {
        if ($currentMethodName === $attributeValue) {
            return null;
        }
        $classMethod = $currentClass->getMethod($attributeValue);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        return $attributeValue;
    }
    private function resolveDependsCloneClassMethod(Class_ $currentClass, string $currentMethodName, string $attributeValue) : ?string
    {
        if (\strncmp($attributeValue, 'clone ', \strlen('clone ')) !== 0) {
            return null;
        }
        [, $attributeValue] = \explode('clone ', $attributeValue);
        if ($currentMethodName === $attributeValue) {
            return null;
        }
        $classMethod = $currentClass->getMethod($attributeValue);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        return $attributeValue;
    }
}
