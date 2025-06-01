<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector\DeprecatedAnnotationToDeprecatedAttributeRectorTest
 */
final class DeprecatedAnnotationToDeprecatedAttributeRector extends AbstractRector implements MinPhpVersionInterface
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
     * @see https://regex101.com/r/qNytVk/1
     * @var string
     */
    private const VERSION_MATCH_REGEX = '/^(?:(\\d+\\.\\d+\\.\\d+)\\s+)?(.*)$/';
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @deprecated annotation to Deprecated attribute', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @deprecated 1.0.0 Use SomeOtherClass instead
 */
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Deprecated(message: 'Use SomeOtherClass instead', since: '1.0.0')]
class SomeClass
{
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
/**
 * @deprecated 1.0.0 Use SomeOtherFunction instead
 */
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Deprecated(message: 'Use SomeOtherFunction instead', since: '1.0.0')]
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class, ClassConst::class];
    }
    /**
     * @param ClassConst|Function_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $deprecatedAttributeGroup = $this->handleDeprecated($phpDocInfo);
            if ($deprecatedAttributeGroup instanceof AttributeGroup) {
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                $node->attrGroups = \array_merge($node->attrGroups, [$deprecatedAttributeGroup]);
                $this->removeDeprecatedAnnotations($phpDocInfo);
                $hasChanged = \true;
            }
        }
        return $hasChanged ? $node : null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATED_ATTRIBUTE;
    }
    private function handleDeprecated(PhpDocInfo $phpDocInfo) : ?AttributeGroup
    {
        $attributeGroup = null;
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('deprecated');
        foreach ($desiredTagValueNodes as $desiredTagValueNode) {
            if (!$desiredTagValueNode->value instanceof DeprecatedTagValueNode) {
                continue;
            }
            $attributeGroup = $this->createAttributeGroup($desiredTagValueNode->value->description);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $desiredTagValueNode);
            break;
        }
        return $attributeGroup;
    }
    private function createAttributeGroup(string $annotationValue) : AttributeGroup
    {
        $matches = Strings::match($annotationValue, self::VERSION_MATCH_REGEX);
        $since = $matches[1] ?? null;
        $message = $matches[2] ?? null;
        return $this->phpAttributeGroupFactory->createFromClassWithItems('Deprecated', \array_filter(['message' => $message, 'since' => $since]));
    }
    private function removeDeprecatedAnnotations(PhpDocInfo $phpDocInfo) : bool
    {
        $hasChanged = \false;
        $desiredTagValueNodes = $phpDocInfo->getTagsByName('deprecated');
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
