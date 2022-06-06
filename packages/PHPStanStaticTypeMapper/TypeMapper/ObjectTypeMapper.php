<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix20220606\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<ObjectType>
 */
final class ObjectTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return ObjectType::class;
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        if ($type instanceof ShortenedObjectType) {
            return new IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof AliasedObjectType) {
            return new IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof GenericObjectType) {
            return $this->mapGenericObjectType($type, $typeKind);
        }
        if ($type instanceof NonExistingObjectType) {
            // possibly generic type
            return new IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof FullyQualifiedObjectType && \strncmp($type->getClassName(), '\\', \strlen('\\')) === 0) {
            return new IdentifierTypeNode($type->getClassName());
        }
        return new IdentifierTypeNode('\\' . $type->getClassName());
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
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
            $className = $type->getClassName();
            if (\strncmp($className, '\\', \strlen('\\')) === 0) {
                // skip leading \
                return new FullyQualified(Strings::substring($className, 1));
            }
            return new FullyQualified($className);
        }
        if (!$type instanceof GenericObjectType) {
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
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @param TypeKind::* $typeKind
     */
    private function mapGenericObjectType(GenericObjectType $genericObjectType, string $typeKind) : TypeNode
    {
        $name = $this->resolveGenericObjectTypeName($genericObjectType);
        $identifierTypeNode = new IdentifierTypeNode($name);
        $genericTypeNodes = [];
        foreach ($genericObjectType->getTypes() as $key => $genericType) {
            // mixed type on 1st item in iterator has no value
            if ($name === 'Iterator' && $genericType instanceof MixedType && $key === 0) {
                continue;
            }
            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericType, $typeKind);
            $genericTypeNodes[] = $typeNode;
        }
        if ($genericTypeNodes === []) {
            return $identifierTypeNode;
        }
        return new GenericTypeNode($identifierTypeNode, $genericTypeNodes);
    }
    private function resolveGenericObjectTypeName(GenericObjectType $genericObjectType) : string
    {
        if ($genericObjectType instanceof FullyQualifiedGenericObjectType) {
            return '\\' . $genericObjectType->getClassName();
        }
        if (\strpos($genericObjectType->getClassName(), '\\') !== \false) {
            return '\\' . $genericObjectType->getClassName();
        }
        return $genericObjectType->getClassName();
    }
}
