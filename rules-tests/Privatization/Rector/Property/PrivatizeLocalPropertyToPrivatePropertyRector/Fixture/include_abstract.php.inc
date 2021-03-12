<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

abstract class IncludeAbstract
{
    public $property;
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

abstract class IncludeAbstract
{
    private $property;
}

?>
