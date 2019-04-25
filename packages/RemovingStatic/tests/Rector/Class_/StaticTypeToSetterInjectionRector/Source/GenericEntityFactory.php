<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source;

use phpDocumentor\Reflection\Types\Integer;

final class GenericEntityFactory
{
    public static function make(): Integer
    {
        return 5;
    }
}
