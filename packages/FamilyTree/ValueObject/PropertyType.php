<?php

declare(strict_types=1);

namespace Rector\FamilyTree\ValueObject;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PHPStan\Type\Type;

final class PropertyType
{
    public function __construct(
        private readonly Type $varType,
        private readonly Name|ComplexType|null $propertyTypeNode
    ) {
    }

    public function getVarType(): Type
    {
        return $this->varType;
    }

    public function getPropertyTypeNode(): Name|ComplexType|null
    {
        return $this->propertyTypeNode;
    }
}
