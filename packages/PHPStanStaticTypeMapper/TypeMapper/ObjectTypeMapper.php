<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix202301\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix202301\Symfony\Contracts\Service\Attribute\Required;
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
        if ($type instanceof NonExistingObjectType) {
            return null;
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
