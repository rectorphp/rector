<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

final class IdentifierTypeMapper implements PhpDocTypeMapperInterface
{
    public function __construct(
        private ObjectTypeSpecifier $objectTypeSpecifier,
        private ScalarStringToTypeMapper $scalarStringToTypeMapper,
        private ParentClassScopeResolver $parentClassScopeResolver
    ) {
    }

    /**
     * @return class-string<TypeNode>
     */
    public function getNodeType(): string
    {
        return IdentifierTypeNode::class;
    }

    /**
     * @param IdentifierTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        $type = $this->scalarStringToTypeMapper->mapScalarStringToType($typeNode->name);
        if (! $type instanceof MixedType) {
            return $type;
        }

        if ($type->isExplicitMixed()) {
            return $type;
        }

        $loweredName = strtolower($typeNode->name);

        if ($loweredName === 'class-string') {
            return new ClassStringType();
        }

        if ($loweredName === 'self') {
            return $this->mapSelf($node);
        }

        if ($loweredName === 'parent') {
            return $this->mapParent($node);
        }

        if ($loweredName === 'static') {
            return $this->mapStatic($node);
        }

        if ($loweredName === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }

        $objectType = new ObjectType($typeNode->name);

        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, $objectType);
    }

    private function mapSelf(Node $node): MixedType | SelfObjectType
    {
        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            // self outside the class, e.g. in a function
            return new MixedType();
        }

        return new SelfObjectType($className);
    }

    private function mapParent(Node $node): ParentStaticType | MixedType
    {
        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($node);
        if ($parentClassName !== null) {
            return new ParentStaticType($parentClassName);
        }

        return new MixedType();
    }

    private function mapStatic(Node $node): MixedType | StaticType
    {
        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return new MixedType();
        }

        return new StaticType($className);
    }
}
