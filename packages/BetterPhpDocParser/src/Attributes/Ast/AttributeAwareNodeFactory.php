<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Ast\NodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareInvalidTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareMethodTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTextNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePropertyTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareThrowsTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareCallableTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareGenericTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareNullableTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareThisTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareUnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Exception\NotImplementedYetException;
use Rector\BetterPhpDocParser\Exception\ShouldNotHappenException;

final class AttributeAwareNodeFactory
{
    /**
     * @var NodeTraverser
     */
    private $nodeTraverser;

    public function __construct(NodeTraverser $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
    }

    /**
     * @return PhpDocNode|PhpDocChildNode|PhpDocTagValueNode AttributeAwareNodeInterface
     */
    public function createFromNode(Node $node): AttributeAwareNodeInterface
    {
        if ($node instanceof AttributeAwareNodeInterface) {
            return $node;
        }

        if ($node instanceof PhpDocNode) {
            $this->nodeTraverser->traverseWithCallable($node, function (Node $node) {
                if ($node instanceof AttributeAwareNodeInterface) {
                    return $node;
                }

                return $this->createFromNode($node);
            });

            return new AttributeAwarePhpDocNode($node->children);
        }

        if ($node instanceof PhpDocTagNode) {
            return new AttributeAwarePhpDocTagNode($node->name, $node->value);
        }

        if ($node instanceof PhpDocTextNode) {
            return new AttributeAwarePhpDocTextNode($node->text);
        }

        if ($node instanceof PhpDocTagValueNode) {
            return $this->createFromPhpDocValueNode($node);
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
                $phpDocTagValueNode->description
            );
        }

        if ($phpDocTagValueNode instanceof MethodTagValueNode) {
            $typeNode = $phpDocTagValueNode->returnType ? $this->createFromTypeNode(
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

        if ($phpDocTagValueNode instanceof GenericTagValueNode) {
            return new AttributeAwareGenericTagValueNode($phpDocTagValueNode->value);
        }

        if ($phpDocTagValueNode instanceof InvalidTagValueNode) {
            return new AttributeAwareInvalidTagValueNode($phpDocTagValueNode->value, $phpDocTagValueNode->exception);
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
        if ($typeNode instanceof IdentifierTypeNode) {
            return new AttributeAwareIdentifierTypeNode($typeNode->name);
        }

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

        if ($typeNode instanceof ThisTypeNode) {
            return new AttributeAwareThisTypeNode();
        }

        if ($typeNode instanceof CallableTypeNode) {
            return new AttributeAwareCallableTypeNode(
                $typeNode->identifier,
                $typeNode->parameters,
                $typeNode->returnType
            );
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
