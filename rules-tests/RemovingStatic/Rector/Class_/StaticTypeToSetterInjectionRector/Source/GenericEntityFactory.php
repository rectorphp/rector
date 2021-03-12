<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Source;

use phpDocumentor\Reflection\Types\Integer;

final class GenericEntityFactory
{
    public static function make(): Integer
    {
        return 5;
    }
}
