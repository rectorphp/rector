<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\Fixture;

final class StaticProperty
{
    public static $value;
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\Fixture;

final class StaticProperty
{
    public $value;
}

?>
