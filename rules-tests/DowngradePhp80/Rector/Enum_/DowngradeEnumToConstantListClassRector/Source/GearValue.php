<?php
declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Source;

enum GearValue
{
    case FIRST;

    case SECOND;
}
