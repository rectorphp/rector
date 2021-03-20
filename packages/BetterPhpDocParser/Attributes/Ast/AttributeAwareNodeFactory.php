<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SpacingAwareTemplateTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\SpacingAwareCallableTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\BracketsAwareUnionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\SpacingAwareArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\PhpDocNodeTransformerInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class AttributeAwareNodeFactory
{
    /**
     * @var PhpDocNodeTransformerInterface[]
     */
    private $phpDocNodeTransformers = [];

    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @param PhpDocNodeTransformerInterface[] $phpDocNodeTransformers
     */
    public function __construct(array $phpDocNodeTransformers, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        foreach ($phpDocNodeTransformers as $phpDocNodeTransformer) {
            // prevents cyclic dependency
            if ($phpDocNodeTransformer instanceof AttributeAwareNodeFactoryAwareInterface) {
                $phpDocNodeTransformer->setAttributeAwareNodeFactory($this);
            }
        }

        $this->phpDocNodeTransformers = $phpDocNodeTransformers;
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

            foreach ($this->phpDocNodeTransformers as $phpDocNodeTransformer) {
                if (! $phpDocNodeTransformer->isMatch($node)) {
                    continue;
                }

                return $phpDocNodeTransformer->transform($node, $docContent);
            }

            return $node;
        });

        return $node;
    }
}
