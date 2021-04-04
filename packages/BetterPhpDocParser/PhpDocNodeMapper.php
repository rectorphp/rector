<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ParentNodeTraverser;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayShapeItemNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var TokenIteratorFactory
     */
    private $tokenIteratorFactory;

    /**
     * @var ParentNodeTraverser
     */
    private $parentNodeTraverser;

    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        TokenIteratorFactory $tokenIteratorFactory,
        ParentNodeTraverser $parentNodeTraverser
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->parentNodeTraverser = $parentNodeTraverser;
    }

    /**
     * @template T of \PHPStan\PhpDocParser\Ast\Node
     * @param T $node
     * @return T
     */
    public function transform(Node $node, string $docContent): Node
    {
        $node = $this->parentNodeTraverser->transform($node, $docContent);

        $betterTokenIterator = $this->tokenIteratorFactory->create($docContent);

        // connect parent types with children types
        $transformingCallable = function (Node $node, string $docContent) use ($betterTokenIterator): Node {
            // narrow to node-specific doc content
            $startAndEnd = $node->getAttribute(StartAndEnd::class);
            if ($startAndEnd instanceof StartAndEnd) {
                $parentTypeNode = $node->getAttribute('parent');
                if ($parentTypeNode instanceof ArrayTypeNode) {
                    $docContent = $betterTokenIterator->printFromTo(
                        $startAndEnd->getStart() - 1,
                        $startAndEnd->getEnd() + 1
                    );
                } else {
                    $docContent = $betterTokenIterator->printFromTo($startAndEnd->getStart(), $startAndEnd->getEnd());
                }
            }

            if ($node instanceof IntersectionTypeNode && ! $node instanceof BracketsAwareIntersectionTypeNode) {
                $bracketsAwareIntersectionTypeNode = new BracketsAwareIntersectionTypeNode($node->types);

                $this->mirrorAttributes($node, $bracketsAwareIntersectionTypeNode);
                return $bracketsAwareIntersectionTypeNode;
            }

            if ($node instanceof ArrayTypeNode && ! $node instanceof SpacingAwareArrayTypeNode) {
                $spacingAwareArrayTypeNode = new SpacingAwareArrayTypeNode($node->type);

                $this->mirrorAttributes($node, $spacingAwareArrayTypeNode);
                return $spacingAwareArrayTypeNode;
            }

            if ($node instanceof CallableTypeNode && ! $node instanceof SpacingAwareCallableTypeNode) {
                $spacingAwareCallableTypeNode = new SpacingAwareCallableTypeNode(
                    $node->identifier,
                    $node->parameters,
                    $node->returnType
                );

                $this->mirrorAttributes($node, $spacingAwareCallableTypeNode);
                return $spacingAwareCallableTypeNode;
            }

            if ($node instanceof UnionTypeNode && ! $node instanceof BracketsAwareUnionTypeNode) {
                // if has parent of array, widen the type
                $bracketsAwareUnionTypeNode = new BracketsAwareUnionTypeNode($node->types, $docContent);

                $this->mirrorAttributes($node, $bracketsAwareUnionTypeNode);
                return $bracketsAwareUnionTypeNode;
            }

            if ($node instanceof ArrayShapeItemNode && ! $node instanceof SpacingAwareArrayShapeItemNode) {
                $spacingAwareArrayShapeItemNode = new SpacingAwareArrayShapeItemNode(
                    $node->keyName, $node->optional, $node->valueType, $docContent
                );

                $this->mirrorAttributes($node, $spacingAwareArrayShapeItemNode);
                return $spacingAwareArrayShapeItemNode;
            }

            if ($node instanceof TemplateTagValueNode && ! $node instanceof SpacingAwareTemplateTagValueNode) {
                $spacingAwareTemplateTagValueNode = new SpacingAwareTemplateTagValueNode(
                    $node->name,
                    $node->bound,
                    $node->description,
                    $docContent
                );

                $this->mirrorAttributes($node, $spacingAwareTemplateTagValueNode);
                return $spacingAwareTemplateTagValueNode;
            }

            if ($node instanceof ParamTagValueNode && ! $node instanceof VariadicAwareParamTagValueNode) {
                $variadicAwareParamTagValueNode = new VariadicAwareParamTagValueNode(
                    $node->type, $node->isVariadic, $node->parameterName, $node->description
                );

                $this->mirrorAttributes($node, $variadicAwareParamTagValueNode);
                return $variadicAwareParamTagValueNode;
            }

            return $node;
        };

        return $this->phpDocNodeTraverser->traverseWithCallable($node, $docContent, $transformingCallable);
    }

    private function mirrorAttributes(Node $oldNode, Node $newNode): void
    {
        $newNode->setAttribute(StartAndEnd::class, $oldNode->getAttribute(StartAndEnd::class));
    }
}
