<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class ObjectTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return ObjectType::class;
    }

    /**
     * @param ObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        if ($type instanceof ShortenedObjectType) {
            return new IdentifierTypeNode($type->getClassName());
        }

        if ($type instanceof AliasedObjectType) {
            return new IdentifierTypeNode($type->getClassName());
        }

        return new IdentifierTypeNode('\\' . $type->getClassName());
    }

    /**
     * @param ObjectType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($type instanceof SelfObjectType) {
            return new Identifier('self');
        }

        if ($type instanceof ShortenedObjectType) {
            return new FullyQualified($type->getFullyQualifiedName());
        }

        if ($type instanceof AliasedObjectType) {
            return new Name($type->getClassName());
        }

        if ($type instanceof FullyQualifiedObjectType) {
            return new FullyQualified($type->getClassName());
        }

        if ($type instanceof GenericObjectType && $type->getClassName() === 'object') {
            return new Identifier('object');
        }

        // fallback
        return new FullyQualified($type->getClassName());
    }

    /**
     * @param ObjectType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        if ($type instanceof AliasedObjectType) {
            // no preslash for alias
            return $type->getClassName();
        }

        if ($type instanceof ShortenedObjectType) {
            return '\\' . $type->getFullyQualifiedName();
        }

        if ($type instanceof FullyQualifiedObjectType) {
            // always prefixed with \\
            return '\\' . $type->getClassName();
        }

        if (ClassExistenceStaticHelper::doesClassLikeExist($type->getClassName())) {
            // FQN by default
            return '\\' . $type->describe(VerbosityLevel::typeOnly());
        }

        return $type->getClassName();
    }
}
