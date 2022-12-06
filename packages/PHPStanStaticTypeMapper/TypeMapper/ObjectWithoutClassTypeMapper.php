<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\TemplateObjectWithoutClassType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\EmptyGenericTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\PHPStan\ObjectWithoutClassTypeWithParentTypes;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType;
/**
 * @implements TypeMapperInterface<ObjectWithoutClassType>
 */
final class ObjectWithoutClassTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return ObjectWithoutClassType::class;
    }
    /**
     * @param ObjectWithoutClassType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        if ($type instanceof ParentObjectWithoutClassType) {
            return new IdentifierTypeNode('parent');
        }
        if ($type instanceof TemplateObjectWithoutClassType) {
            $attributeAwareIdentifierTypeNode = new IdentifierTypeNode($type->getName());
            return new EmptyGenericTypeNode($attributeAwareIdentifierTypeNode);
        }
        // special case for anonymous classes that implement another type
        if ($type instanceof ObjectWithoutClassTypeWithParentTypes) {
            $parentTypes = $type->getParentTypes();
            if (\count($parentTypes) === 1) {
                $parentType = $parentTypes[0];
                return new FullyQualifiedIdentifierTypeNode($parentType->getClassName());
            }
        }
        return new IdentifierTypeNode('object');
    }
    /**
     * @param ObjectWithoutClassType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        // special case for anonymous classes that implement another type
        if ($type instanceof ObjectWithoutClassTypeWithParentTypes) {
            $parentTypes = $type->getParentTypes();
            if (\count($parentTypes) === 1) {
                $parentType = $parentTypes[0];
                return new FullyQualified($parentType->getClassName());
            }
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::OBJECT_TYPE)) {
            return null;
        }
        return new Name('object');
    }
}
