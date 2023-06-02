<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\PhpDoc\ShortClassExpander;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class CollectionTypeResolver
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Naming\NameScopeFactory
     */
    private $nameScopeFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDoc\ShortClassExpander
     */
    private $shortClassExpander;
    public function __construct(NameScopeFactory $nameScopeFactory, PhpDocInfoFactory $phpDocInfoFactory, ShortClassExpander $shortClassExpander)
    {
        $this->nameScopeFactory = $nameScopeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->shortClassExpander = $shortClassExpander;
    }
    public function resolveFromTypeNode(TypeNode $typeNode, Node $node) : ?FullyQualifiedObjectType
    {
        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $unionedTypeNode) {
                $resolvedUnionedType = $this->resolveFromTypeNode($unionedTypeNode, $node);
                if ($resolvedUnionedType instanceof FullyQualifiedObjectType) {
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
    public function resolveFromOneToManyProperty(Property $property) : ?FullyQualifiedObjectType
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\OneToMany');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue('targetEntity');
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $targetEntityClass = $targetEntityArrayItemNode->value;
        if ($targetEntityClass instanceof StringNode) {
            $targetEntityClass = $targetEntityClass->value;
        }
        if (!\is_string($targetEntityClass)) {
            return null;
        }
        $fullyQualifiedTargetEntity = $this->shortClassExpander->resolveFqnTargetEntity($targetEntityClass, $property);
        return new FullyQualifiedObjectType($fullyQualifiedTargetEntity);
    }
}
