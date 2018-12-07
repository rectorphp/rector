<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    public $toBeProtectedProperty;

    protected $toBePrivateProperty;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Fixture;

use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    protected $toBeProtectedProperty;

    private $toBePrivateProperty;
}

?>
