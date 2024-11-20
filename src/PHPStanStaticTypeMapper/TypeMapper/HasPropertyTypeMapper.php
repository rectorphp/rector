<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasPropertyType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasPropertyType>
 */
final class HasPropertyTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper;
    public function __construct(\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper)
    {
        $this->objectWithoutClassTypeMapper = $objectWithoutClassTypeMapper;
    }
    public function getNodeClass() : string
    {
        return HasPropertyType::class;
    }
    /**
     * @param HasPropertyType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param HasPropertyType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return $this->objectWithoutClassTypeMapper->mapToPhpParserNode($type, $typeKind);
    }
}
