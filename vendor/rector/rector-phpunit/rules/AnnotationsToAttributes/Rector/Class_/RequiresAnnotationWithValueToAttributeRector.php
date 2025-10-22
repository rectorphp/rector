<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\AnnotationsToAttributes\NodeFactory\RequiresAttributeFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\Class_\RequiresAnnotationWithValueToAttributeRector\RequiresAnnotationWithValueToAttributeRectorTest
 */
final class RequiresAnnotationWithValueToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private RequiresAttributeFactory $requiresAttributeFactory;
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
    public function __construct(PhpDocTagRemover $phpDocTagRemover, RequiresAttributeFactory $requiresAttributeFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->requiresAttributeFactory = $requiresAttributeFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Requires annotations with values to attributes', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

/**
 * @requires PHP > 8.4
 * @requires PHPUnit >= 10
 */

final class SomeTest extends TestCase
{
    /**
     * @requires setting date.timezone Europe/Berlin
     */
    public function test()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\RequiresPhp('> 8.4')]
#[\PHPUnit\Framework\Attributes\RequiresPhpunit('>= 10')]
final class SomeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\RequiresSetting('date.timezone', 'Europe/Berlin')]
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
    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class];
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        $hasChanged = \false;
        if ($node instanceof Class_) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            if ($phpDocInfo instanceof PhpDocInfo) {
                $requiresAttributeGroups = $this->handleRequires($phpDocInfo);
                if ($requiresAttributeGroups !== []) {
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                    $node->attrGroups = array_merge($node->attrGroups, $requiresAttributeGroups);
                    $this->removeMethodRequiresAnnotations($phpDocInfo);
                    $hasChanged = \true;
                }
            }
        }
        return $hasChanged ? $node : null;
    }
    /**
     * @return array<string, AttributeGroup|null>
     */
    private function handleRequires(PhpDocInfo $phpDocInfo): array
    {
        $attributeGroups = [];
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('requires');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $requires = $desiredTagValueNode->value->value;
            $attributeGroups[$requires] = $this->requiresAttributeFactory->create($requires);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        return $attributeGroups;
    }
    private function removeMethodRequiresAnnotations(PhpDocInfo $phpDocInfo): bool
    {
        $hasChanged = \false;
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('requires');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $requiresAttributeGroups = $this->handleRequires($phpDocInfo);
        if ($requiresAttributeGroups === []) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        $classMethod->attrGroups = array_merge($classMethod->attrGroups, $requiresAttributeGroups);
        $this->removeMethodRequiresAnnotations($phpDocInfo);
        return $classMethod;
    }
}
