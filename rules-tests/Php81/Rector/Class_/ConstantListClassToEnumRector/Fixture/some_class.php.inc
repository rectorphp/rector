<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

enum Direction : string
{
    case LEFT = 'left';
    case RIGHT = 'right';
}

?>
