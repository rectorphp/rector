<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\NodeDecorator\StringsTypePhpDocNodeDecorator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocModifier
{
    /**
     * @var StringsTypePhpDocNodeDecorator
     */
    private $stringsTypePhpDocNodeDecorator;

    public function __construct(StringsTypePhpDocNodeDecorator $stringsTypePhpDocNodeDecorator)
    {
        $this->stringsTypePhpDocNodeDecorator = $stringsTypePhpDocNodeDecorator;
    }

    public function removeTagByName(PhpDocInfo $phpDocInfo, string $tagName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $tagName = AnnotationNaming::normalizeName($tagName);

        $phpDocTagNodes = $phpDocInfo->getTagsByName($tagName);

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
        }
    }

    public function removeTagByNameAndContent(PhpDocInfo $phpDocInfo, string $tagName, string $tagContent): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $tagName = AnnotationNaming::normalizeName($tagName);
        $phpDocTagNodes = $phpDocNode->getTagsByName($tagName);

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            if (! $phpDocTagNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $phpDocTagNode->value instanceof PhpDocTagValueNode) {
                continue;
            }

            // e.g. @method someMethod(), only matching content is enough, due to real case usability
            if (Strings::contains((string) $phpDocTagNode->value, $tagContent)) {
                $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
            }
        }
    }

    public function removeParamTagByParameter(PhpDocInfo $phpDocInfo, string $parameterName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        /** @var PhpDocTagNode[] $phpDocTagNodes */
        $phpDocTagNodes = $phpDocNode->getTagsByName('@param');

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            /** @var ParamTagValueNode|InvalidTagValueNode $paramTagValueNode */
            $paramTagValueNode = $phpDocTagNode->value;

            $parameterName = '$' . ltrim($parameterName, '$');

            // process invalid tag values
            if ($paramTagValueNode instanceof InvalidTagValueNode) {
                if ($paramTagValueNode->value === $parameterName) {
                    $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
                }
                // process normal tag
            } elseif ($paramTagValueNode->parameterName === $parameterName) {
                $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
            }
        }
    }

    /**
     * @param PhpDocTagNode|PhpDocTagValueNode $phpDocTagOrPhpDocTagValueNode
     */
    public function removeTagFromPhpDocNode(PhpDocNode $phpDocNode, $phpDocTagOrPhpDocTagValueNode): void
    {
        // remove specific tag
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
                return;
            }
        }

        // or by type
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
            }
        }
    }

    public function replaceTagByAnother(PhpDocNode $phpDocNode, string $oldTag, string $newTag): void
    {
        $oldTag = AnnotationNaming::normalizeName($oldTag);
        $newTag = AnnotationNaming::normalizeName($newTag);

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->name === $oldTag) {
                $phpDocChildNode->name = $newTag;
            }
        }
    }

    public function replacePhpDocTypeByAnother(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        string $oldType,
        string $newType,
        Node $node
    ): AttributeAwarePhpDocNode {
        foreach ($attributeAwarePhpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $this->isTagValueNodeWithType($phpDocChildNode)) {
                continue;
            }

            /** @var VarTagValueNode|ParamTagValueNode|ReturnTagValueNode $tagValueNode */
            $tagValueNode = $phpDocChildNode->value;

            $phpDocChildNode->value->type = $this->replaceTypeNode($tagValueNode->type, $oldType, $newType);

            $this->stringsTypePhpDocNodeDecorator->decorate($attributeAwarePhpDocNode, $node);
        }

        return $attributeAwarePhpDocNode;
    }

    private function isTagValueNodeWithType(PhpDocTagNode $phpDocTagNode): bool
    {
        return $phpDocTagNode->value instanceof ParamTagValueNode ||
            $phpDocTagNode->value instanceof VarTagValueNode ||
            $phpDocTagNode->value instanceof ReturnTagValueNode;
    }

    private function replaceTypeNode(TypeNode $typeNode, string $oldType, string $newType): TypeNode
    {
        if ($typeNode instanceof AttributeAwareIdentifierTypeNode) {
            $nodeType = $this->resolveNodeType($typeNode);

            if (is_a($nodeType, $oldType, true) || ltrim($nodeType, '\\') === $oldType) {
                $newType = $this->forceFqnPrefix($newType);

                return new AttributeAwareIdentifierTypeNode($newType);
            }
        }

        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->replaceTypeNode($subTypeNode, $oldType, $newType);
            }
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $typeNode->type = $this->replaceTypeNode($typeNode->type, $oldType, $newType);

            return $typeNode;
        }

        return $typeNode;
    }

    /**
     * @param AttributeAwareNodeInterface&TypeNode $typeNode
     */
    private function resolveNodeType(TypeNode $typeNode): string
    {
        $nodeType = $typeNode->getAttribute('resolved_name');

        if ($nodeType === null) {
            $nodeType = $typeNode->getAttribute(Attribute::TYPE_AS_STRING);
        }

        if ($nodeType === null) {
            $nodeType = $typeNode->name;
        }

        return $nodeType;
    }

    private function forceFqnPrefix(string $newType): string
    {
        if (Strings::contains($newType, '\\')) {
            $newType = '\\' . ltrim($newType, '\\');
        }

        return $newType;
    }
}
