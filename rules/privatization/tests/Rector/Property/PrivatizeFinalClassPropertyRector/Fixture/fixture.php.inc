<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class SomeClass
{
    protected $value;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class SomeClass
{
    private $value;
}

?>
