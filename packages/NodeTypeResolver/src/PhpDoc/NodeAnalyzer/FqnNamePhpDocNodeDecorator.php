<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Ast\NodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareThrowsTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareGenericTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareThisTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareUnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute as PhpDocAttribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeDecoratorInterface;

final class FqnNamePhpDocNodeDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @var string
     */
    public const RESOLVED_NAMES = 'resolved_names';

    /**
     * @var string
     */
    private const RESOLVED_NAME = 'resolved_name';

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(NamespaceAnalyzer $namespaceAnalyzer, NodeTraverser $nodeTraverser)
    {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->nodeTraverser = $nodeTraverser;
    }

    public function decorate(AttributeAwarePhpDocNode $attributeAwarePhpDocNode, Node $node): AttributeAwarePhpDocNode
    {
        $this->nodeTraverser->traverseWithCallable(
            $attributeAwarePhpDocNode,
            function (AttributeAwareNodeInterface $attributeAwarePhpDocNode) use ($node) {
                if (! $attributeAwarePhpDocNode instanceof IdentifierTypeNode) {
                    return $attributeAwarePhpDocNode;
                }

                if (! $this->isClassyType($attributeAwarePhpDocNode->name)) {
                    return $attributeAwarePhpDocNode;
                }

                $fqnName = $this->namespaceAnalyzer->resolveTypeToFullyQualified(
                    $attributeAwarePhpDocNode->name,
                    $node
                );

                $attributeAwarePhpDocNode->setAttribute(self::RESOLVED_NAME, $fqnName);

                return $attributeAwarePhpDocNode;
            }
        );

        // collect to particular node types
        $this->nodeTraverser->traverseWithCallable(
            $attributeAwarePhpDocNode,
            function (AttributeAwareNodeInterface $attributeAwarePhpDocNode) {
                if (! $this->isTypeAwareNode($attributeAwarePhpDocNode)) {
                    return $attributeAwarePhpDocNode;
                }

                /** @var AttributeAwareVarTagValueNode $attributeAwarePhpDocNode */
                $resolvedNames = $this->collectResolvedNames($attributeAwarePhpDocNode->type);
                $attributeAwarePhpDocNode->setAttribute(self::RESOLVED_NAMES, $resolvedNames);

                $attributeAwarePhpDocNode->type->setAttribute(self::RESOLVED_NAMES, $resolvedNames);

                return $attributeAwarePhpDocNode;
            }
        );

        return $attributeAwarePhpDocNode;
    }

    private function isClassyType(string $name): bool
    {
        return ctype_upper($name[0]);
    }

    private function isTypeAwareNode(AttributeAwareNodeInterface $attributeAwareNode): bool
    {
        return $attributeAwareNode instanceof AttributeAwareVarTagValueNode ||
            $attributeAwareNode instanceof AttributeAwareParamTagValueNode ||
            $attributeAwareNode instanceof AttributeAwareReturnTagValueNode ||
            $attributeAwareNode instanceof AttributeAwareThrowsTagValueNode ||
            $attributeAwareNode instanceof AttributeAwareGenericTypeNode;
    }

    /**
     * @return string[]
     */
    private function collectResolvedNames(TypeNode $typeNode): array
    {
        $resolvedNames = [];
        if ($typeNode instanceof AttributeAwareUnionTypeNode || $typeNode instanceof AttributeAwareIntersectionTypeNode) {
            foreach ($typeNode->types as $subtype) {
                $resolvedNames = array_merge($resolvedNames, $this->collectResolvedNames($subtype));
            }
        } elseif ($typeNode instanceof AttributeAwareArrayTypeNode || $typeNode instanceof AttributeAwareThisTypeNode) {
            $resolvedNames[] = $typeNode->getAttribute(PhpDocAttribute::TYPE_AS_STRING);
        } elseif ($typeNode instanceof AttributeAwareIdentifierTypeNode) {
            if ($typeNode->getAttribute(self::RESOLVED_NAME)) {
                $resolvedNames[] = $typeNode->getAttribute(self::RESOLVED_NAME);
            } elseif ($typeNode->getAttribute(Attribute::TYPE_AS_STRING)) {
                $resolvedNames[] = $typeNode->getAttribute(Attribute::TYPE_AS_STRING);
            }
        }

        return array_filter($resolvedNames);
    }
}
