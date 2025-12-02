<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202512\Webmozart\Assert\Assert;
/**
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector\AnnotationWithValueToAttributeRectorTest
 */
final class AnnotationWithValueToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
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
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var AnnotationWithValueToAttribute[]
     */
    private array $annotationWithValueToAttributes = [];
    private bool $hasChanged = \false;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change annotations with value to attribute', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 */
final class SomeTest extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;

#[BackupGlobals(true)]
final class SomeTest extends TestCase
{
}
CODE_SAMPLE
, [new AnnotationWithValueToAttribute('backupGlobals', 'PHPUnit\Framework\Attributes\BackupGlobals', ['enabled' => \true, 'disabled' => \false])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $this->hasChanged = \false;
        // handle class level
        $this->refactorClassMethodOrClass($node, $node);
        // handle method level
        foreach ($node->getMethods() as $classMethod) {
            $this->refactorClassMethodOrClass($classMethod, $node);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, AnnotationWithValueToAttribute::class);
        $this->annotationWithValueToAttributes = $configuration;
    }
    /**
     * @return mixed
     */
    private function resolveAttributeValue(GenericTagValueNode $genericTagValueNode, AnnotationWithValueToAttribute $annotationWithValueToAttribute)
    {
        $valueMap = $annotationWithValueToAttribute->getValueMap();
        if ($valueMap === []) {
            // no map? convert value as it is
            return $genericTagValueNode->value;
        }
        $originalValue = strtolower($genericTagValueNode->value);
        return $valueMap[$originalValue];
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Class_ $classOrClass
     */
    private function refactorClassMethodOrClass($classOrClass, Class_ $class): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classOrClass);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        foreach ($this->annotationWithValueToAttributes as $annotationWithValueToAttribute) {
            /** @var PhpDocTagNode[] $desiredTagValueNodes */
            $desiredTagValueNodes = $phpDocInfo->getTagsByName($annotationWithValueToAttribute->getAnnotationName());
            foreach ($desiredTagValueNodes as $desiredTagValueNode) {
                if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                $attributeValue = $this->resolveAttributeValue($desiredTagValueNode->value, $annotationWithValueToAttribute);
                $attributeGroup = $this->phpAttributeGroupFactory->createFromClassWithItems($annotationWithValueToAttribute->getAttributeClass(), [$attributeValue]);
                if ($annotationWithValueToAttribute->getIsOnClassLevel()) {
                    $class->attrGroups = array_merge($class->attrGroups, [$attributeGroup]);
                } else {
                    $classOrClass->attrGroups = array_merge($classOrClass->attrGroups, [$attributeGroup]);
                }
                // cleanup
                $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classOrClass);
                $this->hasChanged = \true;
            }
        }
    }
}
