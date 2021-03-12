<?php

namespace Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    public $toBeProtectedProperty;

    protected $toBePrivateProperty;
}

?>
-----
<?php

namespace Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Visibility\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    protected $toBeProtectedProperty;

    private $toBePrivateProperty;
}

?>
