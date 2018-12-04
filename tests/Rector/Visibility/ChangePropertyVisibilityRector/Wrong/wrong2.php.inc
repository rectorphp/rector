<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong;

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    public static $toBePublicStaticProperty;
}

?>
