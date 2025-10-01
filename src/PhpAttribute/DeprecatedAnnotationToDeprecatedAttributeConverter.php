<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use RectorPrefix202510\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
final class DeprecatedAnnotationToDeprecatedAttributeConverter
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
    private const VERSION_MATCH_REGEX = '/^(?:(\d+\.\d+\.\d+)\s+)?(.*)$/';
    /**
     * @see https://regex101.com/r/SVDPOB/1
     * @var string
     */
    private const START_STAR_SPACED_REGEX = '#^ *\*#ms';
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpAttributeGroupFactory $phpAttributeGroupFactory, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Const_ $node
     */
    public function convert($node): ?Node
    {
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $deprecatedAttributeGroup = $this->handleDeprecated($phpDocInfo);
            if ($deprecatedAttributeGroup instanceof AttributeGroup) {
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                $node->attrGroups = array_merge($node->attrGroups, [$deprecatedAttributeGroup]);
                $this->removeDeprecatedAnnotations($phpDocInfo);
                $hasChanged = \true;
            }
        }
        return $hasChanged ? $node : null;
    }
    private function handleDeprecated(PhpDocInfo $phpDocInfo): ?AttributeGroup
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
    private function createAttributeGroup(string $annotationValue): AttributeGroup
    {
        $matches = Strings::match($annotationValue, self::VERSION_MATCH_REGEX);
        if ($matches === null) {
            $annotationValue = Strings::replace($annotationValue, self::START_STAR_SPACED_REGEX, '');
            return new AttributeGroup([new Attribute(new FullyQualified('Deprecated'), [new Arg(new String_($annotationValue, [AttributeKey::KIND => String_::KIND_NOWDOC, AttributeKey::DOC_LABEL => 'TXT']), \false, \false, [], new Identifier('message'))])]);
        }
        $since = $matches[1] ?? null;
        $message = $matches[2] ?? null;
        return $this->phpAttributeGroupFactory->createFromClassWithItems('Deprecated', array_filter(['message' => $message, 'since' => $since]));
    }
    private function removeDeprecatedAnnotations(PhpDocInfo $phpDocInfo): bool
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
