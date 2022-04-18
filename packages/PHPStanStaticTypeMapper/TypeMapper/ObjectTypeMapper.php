<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use RectorPrefix20220418\Nette\Utils\Strings;
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
use RectorPrefix20220418\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<ObjectType>
 */
final class ObjectTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
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
        return \PHPStan\Type\ObjectType::class;
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return $this->mapGenericObjectType($type, $typeKind);
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType) {
            // possibly generic type
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($type->getClassName());
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && \strncmp($type->getClassName(), '\\', \strlen('\\')) === 0) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($type->getClassName());
        }
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('\\' . $type->getClassName());
    }
    /**
     * @param ObjectType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : ?\PhpParser\Node
    {
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType) {
            return new \PhpParser\Node\Name('self');
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return new \PhpParser\Node\Name\FullyQualified($type->getFullyQualifiedName());
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
            return new \PhpParser\Node\Name($type->getClassName());
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            $className = $type->getClassName();
            if (\strncmp($className, '\\', \strlen('\\')) === 0) {
                // skip leading \
                return new \PhpParser\Node\Name\FullyQualified(\RectorPrefix20220418\Nette\Utils\Strings::substring($className, 1));
            }
            return new \PhpParser\Node\Name\FullyQualified($className);
        }
        if (!$type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            // fallback
            return new \PhpParser\Node\Name\FullyQualified($type->getClassName());
        }
        if ($type->getClassName() === 'iterable') {
            // fallback
            return new \PhpParser\Node\Name('iterable');
        }
        if ($type->getClassName() !== 'object') {
            // fallback
            return new \PhpParser\Node\Name\FullyQualified($type->getClassName());
        }
        return new \PhpParser\Node\Name('object');
    }
    /**
     * @required
     */
    public function autowire(\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    private function mapGenericObjectType(\PHPStan\Type\Generic\GenericObjectType $genericObjectType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $name = $this->resolveGenericObjectTypeName($genericObjectType);
        $identifierTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($name);
        $genericTypeNodes = [];
        foreach ($genericObjectType->getTypes() as $key => $genericType) {
            // mixed type on 1st item in iterator has no value
            if ($name === 'Iterator' && $genericType instanceof \PHPStan\Type\MixedType && $key === 0) {
                continue;
            }
            $typeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($genericType, $typeKind);
            $genericTypeNodes[] = $typeNode;
        }
        if ($genericTypeNodes === []) {
            return $identifierTypeNode;
        }
        return new \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode($identifierTypeNode, $genericTypeNodes);
    }
    private function resolveGenericObjectTypeName(\PHPStan\Type\Generic\GenericObjectType $genericObjectType) : string
    {
        if ($genericObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType) {
            return '\\' . $genericObjectType->getClassName();
        }
        if (\strpos($genericObjectType->getClassName(), '\\') !== \false) {
            return '\\' . $genericObjectType->getClassName();
        }
        return $genericObjectType->getClassName();
    }
}
