<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source;

use phpDocumentor\Reflection\Types\Integer;

final class GenericEntityFactoryWithInterface
{
    public static function make(): Integer
    {
        return 5;
    }
}
