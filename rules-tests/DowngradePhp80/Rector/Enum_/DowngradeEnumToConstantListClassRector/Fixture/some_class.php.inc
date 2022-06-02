<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

enum Direction
{
    case LEFT;

    case RIGHT;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector\Fixture;

class Direction
{
    public const LEFT = 'left';
    public const RIGHT = 'right';
}

?>
