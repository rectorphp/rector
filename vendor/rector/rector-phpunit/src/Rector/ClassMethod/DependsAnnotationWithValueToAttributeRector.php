<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector\DependsAnnotationWithValueToAttributeRectorTest
 */
final class DependsAnnotationWithValueToAttributeRector extends AbstractRector implements MinPhpVersionInterface
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
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change depends annotations with value to attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testOne() {}
    public function testTwo() {}
    /**
     * @depends testOne
     * @depends testTwo
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
    public function testTwo() {}
    #[\PHPUnit\Framework\Attributes\Depends('testOne')]
    #[\PHPUnit\Framework\Attributes\Depends('testTwo')]
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
        return [ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param ClassMethod $node
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
        /** @var PhpDocTagNode[] $desiredTagValueNodes */
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('depends');
        if ($desiredTagValueNodes === []) {
            return null;
        }
        $currentClass = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$currentClass instanceof Class_) {
            return null;
        }
        $currentMethodName = $this->getName($node);
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $originalAttributeValue = $desiredTagValueNode->value->value;
            $attributeNameAndValue = $this->resolveAttributeValueAndAttributeName($currentClass, $currentMethodName, $originalAttributeValue);
            if ($attributeNameAndValue === null) {
                continue;
            }
            $attributeGroup = $this->phpAttributeGroupFactory->createFromClassWithItems($attributeNameAndValue[0], [$attributeNameAndValue[1]]);
            $node->attrGroups[] = $attributeGroup;
            // cleanup
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
    /**
     * @return string[]|null
     */
    private function resolveAttributeValueAndAttributeName(Class_ $currentClass, string $currentMethodName, string $originalAttributeValue) : ?array
    {
        // process depends other ClassMethod
        $attributeValue = $this->resolveDependsClassMethod($currentClass, $currentMethodName, $originalAttributeValue);
        $attributeName = 'PHPUnit\\Framework\\Attributes\\Depends';
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
