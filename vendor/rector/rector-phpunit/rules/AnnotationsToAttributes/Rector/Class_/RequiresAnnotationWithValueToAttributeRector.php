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
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
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
        return new RuleDefinition('Change Requires annotations with values to attributes', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

/**
 * @requires PHP > 8.4
 * @requires PHPUnit >= 10
 * @requires OS Windows
 * @requires OSFAMILY Darwin
 * @requires function someFunction
 * @requires function \some\className::someMethod
 * @requires extension mysqli
 * @requires extension mysqli >= 8.3.0
 * @requires setting date.timezone Europe/Berlin
 */

final class SomeTest extends TestCase
{
    /**
     * @requires PHP > 8.4
     * @requires PHPUnit >= 10
     * @requires OS Windows
     * @requires OSFAMILY Darwin
     * @requires function someFunction
     * @requires function \some\className::someMethod
     * @requires extension mysqli
     * @requires extension mysqli >= 8.3.0
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
#[\PHPUnit\Framework\Attributes\RequiresOperatingSystem('Windows')]
#[\PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily('Darwin')]
#[\PHPUnit\Framework\Attributes\RequiresFunction('someFunction')]
#[\PHPUnit\Framework\Attributes\RequiresMethod(\some\className::class, 'someMethod')]
#[\PHPUnit\Framework\Attributes\RequiresPhpExtension('mysqli')]
#[\PHPUnit\Framework\Attributes\RequiresPhpExtension('mysqli', '>= 8.3.0')]
#[\PHPUnit\Framework\Attributes\RequiresSetting('date.timezone', 'Europe/Berlin')]
final class SomeTest extends TestCase
{

    #[\PHPUnit\Framework\Attributes\RequiresPhp('> 8.4')]
    #[\PHPUnit\Framework\Attributes\RequiresPhpunit('>= 10')]
    #[\PHPUnit\Framework\Attributes\RequiresOperatingSystem('Windows')]
    #[\PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily('Darwin')]
    #[\PHPUnit\Framework\Attributes\RequiresFunction('someFunction')]
    #[\PHPUnit\Framework\Attributes\RequiresMethod(\some\className::class, 'someMethod')]
    #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('mysqli')]
    #[\PHPUnit\Framework\Attributes\RequiresPhpExtension('mysqli', '>= 8.3.0')]
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
        $hasChanged = \false;
        if ($node instanceof Class_) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            if ($phpDocInfo instanceof PhpDocInfo) {
                $requiresAttributeGroups = $this->handleRequires($phpDocInfo);
                if ($requiresAttributeGroups !== []) {
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                    $node->attrGroups = \array_merge($node->attrGroups, $requiresAttributeGroups);
                    $this->removeMethodRequiresAnnotations($phpDocInfo);
                    $hasChanged = \true;
                }
            }
            foreach ($node->getMethods() as $classMethod) {
                $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
                if ($phpDocInfo instanceof PhpDocInfo) {
                    $requiresAttributeGroups = $this->handleRequires($phpDocInfo);
                    if ($requiresAttributeGroups !== []) {
                        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
                        $classMethod->attrGroups = \array_merge($classMethod->attrGroups, $requiresAttributeGroups);
                        $this->removeMethodRequiresAnnotations($phpDocInfo);
                        $hasChanged = \true;
                    }
                }
            }
        }
        return $hasChanged ? $node : null;
    }
    private function createAttributeGroup(string $annotationValue) : ?AttributeGroup
    {
        $annotationValues = \explode(' ', $annotationValue, 2);
        $type = \array_shift($annotationValues);
        $attributeValue = \array_shift($annotationValues);
        switch ($type) {
            case 'PHP':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresPhp';
                $attributeValue = [$attributeValue];
                break;
            case 'PHPUnit':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresPhpunit';
                $attributeValue = [$attributeValue];
                break;
            case 'OS':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresOperatingSystem';
                $attributeValue = [$attributeValue];
                break;
            case 'OSFAMILY':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresOperatingSystemFamily';
                $attributeValue = [$attributeValue];
                break;
            case 'function':
                if (\strpos((string) $attributeValue, '::') !== \false) {
                    $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresMethod';
                    $attributeValue = \explode('::', (string) $attributeValue);
                    $attributeValue[0] .= '::class';
                } else {
                    $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresFunction';
                    $attributeValue = [$attributeValue];
                }
                break;
            case 'extension':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresPhpExtension';
                $attributeValue = \explode(' ', (string) $attributeValue, 2);
                break;
            case 'setting':
                $attributeClass = 'PHPUnit\\Framework\\Attributes\\RequiresSetting';
                $attributeValue = \explode(' ', (string) $attributeValue, 2);
                break;
            default:
                return null;
        }
        return $this->phpAttributeGroupFactory->createFromClassWithItems($attributeClass, \array_merge($attributeValue));
    }
    /**
     * @return array<string, AttributeGroup|null>
     */
    private function handleRequires(PhpDocInfo $phpDocInfo) : array
    {
        $attributeGroups = [];
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('requires');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $requires = $desiredTagValueNode->value->value;
            $attributeGroups[$requires] = $this->createAttributeGroup($requires);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
        }
        return $attributeGroups;
    }
    private function removeMethodRequiresAnnotations(PhpDocInfo $phpDocInfo) : bool
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
}
