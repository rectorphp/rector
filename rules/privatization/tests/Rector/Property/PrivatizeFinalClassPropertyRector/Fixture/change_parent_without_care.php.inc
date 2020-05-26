<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

use Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Source\AbstractClassWithProtectedProperty;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedProperty
{
    protected $differentValue = 1000;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

use Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Source\AbstractClassWithProtectedProperty;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedProperty
{
    private $differentValue = 1000;
}

?>
