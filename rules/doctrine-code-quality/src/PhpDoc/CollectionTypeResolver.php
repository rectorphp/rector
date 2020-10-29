<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\PHPStan\NameScopeFactory;

final class CollectionTypeResolver
{
    /**
     * @var NameScopeFactory
     */
    private $nameScopeFactory;

    public function __construct(NameScopeFactory $nameScopeFactory)
    {
        $this->nameScopeFactory = $nameScopeFactory;
    }

    public function resolveFromTypeNode(TypeNode $typeNode, Node $node): ?FullyQualifiedObjectType
    {
        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $unionedTypeNode) {
                $resolvedUnionedType = $this->resolveFromTypeNode($unionedTypeNode, $node);
                if ($resolvedUnionedType !== null) {
                    return $resolvedUnionedType;
                }
            }
        }

        if ($typeNode instanceof ArrayTypeNode && $typeNode->type instanceof IdentifierTypeNode) {
            $nameScope = $this->nameScopeFactory->createNameScopeFromNode($node);
            $fullyQualifiedName = $nameScope->resolveStringName($typeNode->type->name);
            return new FullyQualifiedObjectType($fullyQualifiedName);
        }

        return null;
    }

    public function resolveFromOneToManyProperty(Property $property): ?FullyQualifiedObjectType
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        /** @var OneToManyTagValueNode|null $oneToManyTagValueNode */
        $oneToManyTagValueNode = $phpDocInfo->getByType(OneToManyTagValueNode::class);
        if ($oneToManyTagValueNode === null) {
            return null;
        }

        $fullyQualifiedTargetEntity = $oneToManyTagValueNode->getFullyQualifiedTargetEntity();
        if ($fullyQualifiedTargetEntity === null) {
            throw new ShouldNotHappenException();
        }

        return new FullyQualifiedObjectType($fullyQualifiedTargetEntity);
    }
}
