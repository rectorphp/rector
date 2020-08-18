<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    private static $toBePublicStaticProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInheritsAgain extends ParentObject
{
    public static $toBePublicStaticProperty;
}

?>
