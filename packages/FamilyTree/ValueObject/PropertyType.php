<?php

declare (strict_types=1);
namespace Rector\FamilyTree\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\Type;
final class PropertyType
{
    /**
     * @var \PHPStan\Type\Type
     */
    private $varType;
    /**
     * @var \PhpParser\Node\Name|\PhpParser\Node\NullableType|PhpParserUnionType|null
     */
    private $propertyTypeNode;
    /**
     * @param Name|NullableType|PhpParserUnionType|null $propertyTypeNode
     */
    public function __construct(\PHPStan\Type\Type $varType, ?\PhpParser\Node $propertyTypeNode)
    {
        $this->varType = $varType;
        $this->propertyTypeNode = $propertyTypeNode;
    }
    public function getVarType() : \PHPStan\Type\Type
    {
        return $this->varType;
    }
    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    public function getPropertyTypeNode() : ?\PhpParser\Node
    {
        return $this->propertyTypeNode;
    }
}
