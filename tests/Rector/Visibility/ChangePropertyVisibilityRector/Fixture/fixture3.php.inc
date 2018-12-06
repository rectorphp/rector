<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

class NormalObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

class NormalObject
{
    public static $toBePublicStaticProperty;
}

?>
