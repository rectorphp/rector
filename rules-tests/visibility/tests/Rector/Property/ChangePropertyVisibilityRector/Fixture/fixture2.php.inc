<?php

namespace Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    public static $toBePublicStaticProperty;
}

?>
