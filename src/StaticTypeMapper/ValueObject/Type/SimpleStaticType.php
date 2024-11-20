<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PHPStan\Type\StaticType;
final class SimpleStaticType extends StaticType
{
    /**
     * @readonly
     */
    private string $className;
    public function __construct(string $className)
    {
        $this->className = $className;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
}
