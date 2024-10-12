<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class TypedPropertyFactory
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromPropertyTagValueNode(PropertyTagValueNode $propertyTagValueNode, Class_ $class, string $propertyName) : Property
    {
        $propertyProperty = new PropertyProperty($propertyName);
        $propertyTypeNode = $this->createPropertyTypeNode($propertyTagValueNode, $class);
        return new Property(Class_::MODIFIER_PRIVATE, [$propertyProperty], [], $propertyTypeNode);
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|null
     */
    public function createPropertyTypeNode(PropertyTagValueNode $propertyTagValueNode, Class_ $class, bool $isNullable = \true)
    {
        $propertyType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($propertyTagValueNode->type, $class);
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if ($isNullable && !$typeNode instanceof NullableType && !$typeNode instanceof ComplexType && $typeNode instanceof Node) {
            return new NullableType($typeNode);
        }
        return $typeNode;
    }
}
