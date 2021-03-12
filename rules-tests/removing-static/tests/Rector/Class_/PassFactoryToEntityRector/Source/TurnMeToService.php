<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\PassFactoryToEntityRector\Source;

final class TurnMeToService
{
    public static function someStaticCall()
    {
        return 1;
    }
}
