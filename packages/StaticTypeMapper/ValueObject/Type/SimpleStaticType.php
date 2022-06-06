<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type;

use RectorPrefix20220606\PHPStan\Type\StaticType;
final class SimpleStaticType extends StaticType
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    public function __construct(string $className)
    {
        $this->className = $className;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
}
