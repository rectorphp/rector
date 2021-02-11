<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

abstract class IncludeAbstract
{
    public $property;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

abstract class IncludeAbstract
{
    private $property;
}

?>
