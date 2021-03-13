<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\AttributeAwarePhpDoc\Ast\Type\BetterGenericTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class ObjectTypeMapper implements TypeMapperInterface, PHPStanStaticTypeMapperAwareInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @return class-string<Type>
     */
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

        if ($type instanceof GenericObjectType) {
            return $this->mapGenericObjectType($type);
        }

        return new IdentifierTypeNode('\\' . $type->getClassName());
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

        if (! $type instanceof GenericObjectType) {
            // fallback
            return new FullyQualified($type->getClassName());
        }

        if ($type->getClassName() === 'iterable') {
            // fallback
            return new Name('iterable');
        }

        if ($type->getClassName() !== 'object') {
            // fallback
            return new FullyQualified($type->getClassName());
        }

        return new Name('object');
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

        if ($this->reflectionProvider->hasClass($type->getClassName())) {
            // FQN by default
            return '\\' . $type->describe(VerbosityLevel::typeOnly());
        }

        return $type->getClassName();
    }

    public function setPHPStanStaticTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    private function mapGenericObjectType(GenericObjectType $genericObjectType): GenericTypeNode
    {
        $name = $this->resolveGenericObjectTypeName($genericObjectType);
        $identifierTypeNode = new IdentifierTypeNode($name);

        $genericTypeNodes = [];
        foreach ($genericObjectType->getTypes() as $key => $genericType) {
            // mixed type on 1st item in iterator has no value
            if ($name === 'Iterator' && $genericType instanceof MixedType && $key === 0) {
                continue;
            }

            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericType);
            $genericTypeNodes[] = $typeNode;
        }

        return new BetterGenericTypeNode($identifierTypeNode, $genericTypeNodes);
    }

    private function resolveGenericObjectTypeName(GenericObjectType $genericObjectType): string
    {
        if ($genericObjectType instanceof FullyQualifiedGenericObjectType) {
            return '\\' . $genericObjectType->getClassName();
        }

        if (Strings::contains($genericObjectType->getClassName(), '\\')) {
            return '\\' . $genericObjectType->getClassName();
        }

        return $genericObjectType->getClassName();
    }
}
