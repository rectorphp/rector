<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    public $toBeProtectedProperty;

    protected $toBePrivateProperty;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Fixture;

use Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    protected $toBeProtectedProperty;

    private $toBePrivateProperty;
}

?>
