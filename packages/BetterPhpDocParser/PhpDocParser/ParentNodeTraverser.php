<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class ParentNodeTraverser
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    public function __construct(PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    /**
     * Connects parent types with children types
     */
    public function transform(Node $node, string $docContent): Node
    {
        return $this->phpDocNodeTraverser->traverseWithCallable($node, $docContent, function (Node $node): Node {
            if (! $node instanceof TypeNode) {
                return $node;
            }

            if ($node instanceof ArrayTypeNode || $node instanceof GenericTypeNode || $node instanceof NullableTypeNode) {
                $node->type->setAttribute(PhpDocAttributeKey::PARENT, $node);
            }

            if ($node instanceof CallableTypeNode) {
                $node->returnType->setAttribute(PhpDocAttributeKey::PARENT, $node);
            }

            if ($node instanceof ArrayShapeItemNode) {
                $node->valueType->setAttribute(PhpDocAttributeKey::PARENT, $node);
            }

            if ($node instanceof UnionTypeNode || $node instanceof IntersectionTypeNode) {
                foreach ($node->types as $unionedType) {
                    $unionedType->setAttribute(PhpDocAttributeKey::PARENT, $node);
                }
            }

            if ($node instanceof GenericTypeNode) {
                foreach ($node->genericTypes as $genericType) {
                    $genericType->setAttribute(PhpDocAttributeKey::PARENT, $node);
                }
            }

            return $node;
        });
    }
}
