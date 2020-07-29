<?php

namespace Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    public static $toBePublicStaticProperty;
}

?>
