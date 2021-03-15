<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Class_\PassFactoryToEntityRector\Source;

final class TurnMeToService
{
    public static function someStaticCall()
    {
        return 1;
    }
}
