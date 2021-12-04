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
     * @var \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null
     */
    private $propertyTypeNode;
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null $propertyTypeNode
     */
    public function __construct(\PHPStan\Type\Type $varType, $propertyTypeNode)
    {
        $this->varType = $varType;
        $this->propertyTypeNode = $propertyTypeNode;
    }
    public function getVarType() : \PHPStan\Type\Type
    {
        return $this->varType;
    }
    /**
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null
     */
    public function getPropertyTypeNode()
    {
        return $this->propertyTypeNode;
    }
}
