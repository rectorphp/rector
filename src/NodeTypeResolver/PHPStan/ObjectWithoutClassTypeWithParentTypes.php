<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Type\NativeTypePosition;
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
    public function getParentTypes(): array
    {
        return $this->parentTypes;
    }
    /**
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|null
     */
    public function toNativePhpTypeNode(NativeTypePosition $position)
    {
        // anonymous class implementing a single other type
        if (count($this->parentTypes) === 1) {
            return $this->parentTypes[0]->toNativePhpTypeNode($position);
        }
        return parent::toNativePhpTypeNode($position);
    }
}
