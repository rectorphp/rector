<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareExtendsTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareImplementsTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareMethodTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePropertyTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareThrowsTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareNullableTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactoryCollector;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\Exception\NotImplementedYetException;
use Rector\Exception\ShouldNotHappenException;

/**
 * @see \Rector\BetterPhpDocParser\Tests\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class AttributeAwareNodeFactory
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var AttributeAwareNodeFactoryCollector
     */
    private $attributeAwareNodeFactoryCollector;

    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        AttributeAwareNodeFactoryCollector $attributeAwareNodeFactoryCollector
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->attributeAwareNodeFactoryCollector = $attributeAwareNodeFactoryCollector;
    }

    /**
     * @return PhpDocNode|PhpDocChildNode|PhpDocTagValueNode|AttributeAwareNodeInterface
     */
    public function createFromNode(Node $node): AttributeAwareNodeInterface
    {
        if ($node instanceof AttributeAwareNodeInterface) {
            return $node;
        }

        if ($node instanceof PhpDocNode) {
            $this->phpDocNodeTraverser->traverseWithCallable($node, function (Node $node): AttributeAwareNodeInterface {
                if ($node instanceof AttributeAwareNodeInterface) {
                    return $node;
                }

                return $this->createFromNode($node);
            });

            return new AttributeAwarePhpDocNode($node->children);
        }

        foreach ($this->attributeAwareNodeFactoryCollector->provide() as $attributeNodeAwareFactory) {
            if (! $attributeNodeAwareFactory->isMatch($node)) {
                continue;
            }

            return $attributeNodeAwareFactory->create($node);
        }

        if ($node instanceof PhpDocTagValueNode) {
            return $this->createFromPhpDocValueNode($node);
        }

        if ($node instanceof TypeNode) {
            return $this->createFromTypeNode($node);
        }

        throw new ShouldNotHappenException(sprintf('Node "%s" was missed in "%s".', get_class($node), __METHOD__));
    }

    private function createFromPhpDocValueNode(PhpDocTagValueNode $phpDocTagValueNode): PhpDocTagValueNode
    {
        if ($phpDocTagValueNode instanceof VarTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareVarTagValueNode(
                $typeNode,
                $phpDocTagValueNode->variableName,
                $phpDocTagValueNode->description
            );
        }

        if ($phpDocTagValueNode instanceof ReturnTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareReturnTagValueNode($typeNode, $phpDocTagValueNode->description);
        }

        if ($phpDocTagValueNode instanceof ParamTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareParamTagValueNode(
                $typeNode,
                $phpDocTagValueNode->isVariadic,
                $phpDocTagValueNode->parameterName,
                $phpDocTagValueNode->description,
                false
            );
        }

        if ($phpDocTagValueNode instanceof MethodTagValueNode) {
            $typeNode = $phpDocTagValueNode->returnType !== null ? $this->createFromTypeNode(
                $phpDocTagValueNode->returnType
            ) : null;
            return new AttributeAwareMethodTagValueNode(
                $phpDocTagValueNode->isStatic,
                $typeNode,
                $phpDocTagValueNode->methodName,
                $phpDocTagValueNode->parameters,
                $phpDocTagValueNode->description
            );
        }

        if ($phpDocTagValueNode instanceof PropertyTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwarePropertyTagValueNode(
                $typeNode,
                $phpDocTagValueNode->propertyName,
                $phpDocTagValueNode->description
            );
        }

        if ($phpDocTagValueNode instanceof ExtendsTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareExtendsTagValueNode($typeNode, $phpDocTagValueNode->description);
        }

        if ($phpDocTagValueNode instanceof ImplementsTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareImplementsTagValueNode($typeNode, $phpDocTagValueNode->description);
        }

        if ($phpDocTagValueNode instanceof ThrowsTagValueNode) {
            $typeNode = $this->createFromTypeNode($phpDocTagValueNode->type);
            return new AttributeAwareThrowsTagValueNode($typeNode, $phpDocTagValueNode->description);
        }

        throw new NotImplementedYetException(sprintf(
            'Implement "%s" to "%s"',
            get_class($phpDocTagValueNode),
            __METHOD__
        ));
    }

    /**
     * @return AttributeAwareNodeInterface|TypeNode
     */
    private function createFromTypeNode(TypeNode $typeNode): AttributeAwareNodeInterface
    {
        if ($typeNode instanceof NullableTypeNode) {
            $typeNode->type = $this->createFromTypeNode($typeNode->type);
            return new AttributeAwareNullableTypeNode($typeNode->type);
        }

        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            foreach ($typeNode->types as $i => $subTypeNode) {
                $typeNode->types[$i] = $this->createFromTypeNode($subTypeNode);
            }

            if ($typeNode instanceof UnionTypeNode) {
                return new AttributeAwareUnionTypeNode($typeNode->types);
            }

            return new AttributeAwareIntersectionTypeNode($typeNode->types);
        }

        if ($typeNode instanceof ArrayTypeNode) {
            $typeNode->type = $this->createFromTypeNode($typeNode->type);
            return new AttributeAwareArrayTypeNode($typeNode->type);
        }

        if ($typeNode instanceof GenericTypeNode) {
            /** @var AttributeAwareIdentifierTypeNode $identifierTypeNode */
            $identifierTypeNode = $this->createFromTypeNode($typeNode->type);
            foreach ($typeNode->genericTypes as $key => $genericType) {
                $typeNode->genericTypes[$key] = $this->createFromTypeNode($genericType);
            }

            return new AttributeAwareGenericTypeNode($identifierTypeNode, $typeNode->genericTypes);
        }

        throw new NotImplementedYetException(sprintf('Implement "%s" to "%s"', get_class($typeNode), __METHOD__));
    }
}
