<?php

declare (strict_types=1);
namespace Rector\FamilyTree\ValueObject;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PHPStan\Type\Type;
final class PropertyType
{
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $varType;
    /**
     * @readonly
     * @var \PhpParser\Node\Name|\PhpParser\Node\ComplexType|null
     */
    private $propertyTypeNode;
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|null $propertyTypeNode
     */
    public function __construct(Type $varType, $propertyTypeNode)
    {
        $this->varType = $varType;
        $this->propertyTypeNode = $propertyTypeNode;
    }
    public function getVarType() : Type
    {
        return $this->varType;
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\ComplexType|null
     */
    public function getPropertyTypeNode()
    {
        return $this->propertyTypeNode;
    }
}
