<?php

declare(strict_types=1);

namespace Rector\FamilyTree\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\Type;

final class PropertyType
{
    /**
     * @param Name|NullableType|PhpParserUnionType|null $propertyTypeNode
     */
    public function __construct(
        private Type $varType,
        private ?Node $propertyTypeNode
    ) {
    }

    public function getVarType(): Type
    {
        return $this->varType;
    }

    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    public function getPropertyTypeNode(): ?Node
    {
        return $this->propertyTypeNode;
    }
}
