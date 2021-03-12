<?php

namespace Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    public static $toBePublicStaticProperty;
}

?>
