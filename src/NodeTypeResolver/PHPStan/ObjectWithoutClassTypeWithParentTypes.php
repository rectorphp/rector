<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
final class ObjectWithoutClassTypeWithParentTypes extends ObjectWithoutClassType
{
    /**
     * @var TypeWithClassName[]
     * @readonly
     */
    private array $parentTypes;
    /**
     * @param TypeWithClassName[] $parentTypes
     */
    public function __construct(array $parentTypes, ?Type $subtractedType = null)
    {
        $this->parentTypes = $parentTypes;
        parent::__construct($subtractedType);
    }
    /**
     * @return TypeWithClassName[]
     */
    public function getParentTypes() : array
    {
        return $this->parentTypes;
    }
}
