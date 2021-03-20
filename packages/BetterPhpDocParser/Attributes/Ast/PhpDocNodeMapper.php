<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayShapeItemNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class PhpDocNodeMapper
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
     * @template T of \PHPStan\PhpDocParser\Ast\Node
     * @param T $node
     * @return T
     */
    public function transform(Node $node, string $docContent): Node
    {
        $node = $this->phpDocNodeTraverser->traverseWithCallable($node, $docContent, function (
            Node $node,
            string $docContent
        ): Node {
            if ($node instanceof IntersectionTypeNode && ! $node instanceof BracketsAwareIntersectionTypeNode) {
                return new BracketsAwareIntersectionTypeNode($node->types);
            }

            if ($node instanceof ArrayTypeNode && ! $node instanceof SpacingAwareArrayTypeNode) {
                return new SpacingAwareArrayTypeNode($node->type);
            }

            if ($node instanceof ArrayShapeItemNode && ! $node instanceof SpacingAwareArrayShapeItemNode) {
                return new SpacingAwareArrayShapeItemNode(
                    $node->keyName,
                    $node->optional,
                    $node->valueType,
                    $docContent
                );
            }

            if ($node instanceof CallableTypeNode && ! $node instanceof SpacingAwareCallableTypeNode) {
                return new SpacingAwareCallableTypeNode($node->identifier, $node->parameters, $node->returnType);
            }

            if ($node instanceof UnionTypeNode && ! $node instanceof BracketsAwareUnionTypeNode) {
                return new BracketsAwareUnionTypeNode($node->types, $docContent);
            }

            if ($node instanceof ArrayShapeItemNode && ! $node instanceof SpacingAwareArrayShapeItemNode) {
                return new SpacingAwareArrayShapeItemNode(
                    $node->keyName, $node->optional, $node->valueType, $docContent
                );
            }

            if ($node instanceof TemplateTagValueNode && ! $node instanceof SpacingAwareTemplateTagValueNode) {
                return new SpacingAwareTemplateTagValueNode($node->name, $node->bound, $node->description, $docContent);
            }

            if ($node instanceof ParamTagValueNode && ! $node instanceof VariadicAwareParamTagValueNode) {
                return new VariadicAwareParamTagValueNode(
                    $node->type, $node->isVariadic, $node->parameterName, $node->description
                );
            }

            return $node;
        });

        return $node;
    }
}
