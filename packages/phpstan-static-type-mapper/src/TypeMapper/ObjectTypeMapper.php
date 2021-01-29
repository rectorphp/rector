<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class ObjectTypeMapper implements TypeMapperInterface, PHPStanStaticTypeMapperAwareInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

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
            return new AttributeAwareIdentifierTypeNode($type->getClassName());
        }

        if ($type instanceof AliasedObjectType) {
            return new AttributeAwareIdentifierTypeNode($type->getClassName());
        }

        if ($type instanceof GenericObjectType) {
            if (Strings::contains($type->getClassName(), '\\')) {
                $name = '\\' . $type->getClassName();
            } else {
                $name = $type->getClassName();
            }
            $identifierTypeNode = new IdentifierTypeNode($name);

            $genericTypeNodes = [];
            foreach ($type->getTypes() as $genericType) {
                $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericType);
                $genericTypeNodes[] = $typeNode;
            }

            return new AttributeAwareGenericTypeNode($identifierTypeNode, $genericTypeNodes);
        }

        return new AttributeAwareIdentifierTypeNode('\\' . $type->getClassName());
    }

    /**
     * @param ObjectType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($type instanceof SelfObjectType) {
            return new Name('self');
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
            return new Name('object');
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

    public function setPHPStanStaticTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
}
