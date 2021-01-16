<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\Fixture;

final class StaticProperty
{
    public static $value;
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\Fixture;

final class StaticProperty
{
    public $value;
}

?>
